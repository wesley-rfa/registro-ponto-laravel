<?php

namespace Tests\Unit\Models;

use App\Enums\UserRoleEnum;
use App\Models\ClockIn;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_created_with_valid_data()
    {
        $userData = [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'cpf' => '12345678901',
            'password' => 'password123',
            'job_title' => 'Desenvolvedor',
            'birth_date' => '1990-01-01',
            'postal_code' => '12345-678',
            'address' => 'Rua Teste, 123',
            'role' => UserRoleEnum::EMPLOYEE,
        ];

        $user = User::create($userData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('João Silva', $user->name);
        $this->assertEquals('joao@example.com', $user->email);
        $this->assertEquals('12345678901', $user->cpf);
        $this->assertEquals('Desenvolvedor', $user->job_title);
        $this->assertEquals('1990-01-01', $user->birth_date->format('Y-m-d'));
        $this->assertEquals('12345-678', $user->postal_code);
        $this->assertEquals('Rua Teste, 123', $user->address);
        $this->assertEquals(UserRoleEnum::EMPLOYEE, $user->role);
    }

    public function test_user_casts_birth_date_to_date()
    {
        $user = User::factory()->create([
            'birth_date' => '1990-01-01',
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $user->birth_date);
        $this->assertEquals('1990-01-01', $user->birth_date->format('Y-m-d'));
    }

    public function test_user_casts_role_to_enum()
    {
        $user = User::factory()->create([
            'role' => UserRoleEnum::ADMIN,
        ]);

        $this->assertInstanceOf(UserRoleEnum::class, $user->role);
        $this->assertEquals(UserRoleEnum::ADMIN, $user->role);
    }

    public function test_user_has_creator_relationship()
    {
        $creator = User::factory()->create();
        $user = User::factory()->create(['created_by' => $creator->id]);

        $this->assertInstanceOf(User::class, $user->creator);
        $this->assertEquals($creator->id, $user->creator->id);
    }

    public function test_user_has_created_users_relationship()
    {
        $creator = User::factory()->create();
        $user1 = User::factory()->create(['created_by' => $creator->id]);
        $user2 = User::factory()->create(['created_by' => $creator->id]);

        $createdUsers = $creator->createdUsers;

        $this->assertCount(2, $createdUsers);
        $this->assertTrue($createdUsers->contains($user1));
        $this->assertTrue($createdUsers->contains($user2));
    }

    public function test_user_has_clock_ins_relationship()
    {
        $user = User::factory()->create();
        $clockIn1 = ClockIn::factory()->create(['user_id' => $user->id]);
        $clockIn2 = ClockIn::factory()->create(['user_id' => $user->id]);

        $clockIns = $user->clockIns;

        $this->assertCount(2, $clockIns);
        $this->assertTrue($clockIns->contains($clockIn1));
        $this->assertTrue($clockIns->contains($clockIn2));
    }

    public function test_is_admin_returns_true_for_admin_role()
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);

        $this->assertTrue($admin->isAdmin());
    }

    public function test_is_admin_returns_false_for_employee_role()
    {
        $employee = User::factory()->create(['role' => UserRoleEnum::EMPLOYEE]);

        $this->assertFalse($employee->isAdmin());
    }

    public function test_is_employee_returns_true_for_employee_role()
    {
        $employee = User::factory()->create(['role' => UserRoleEnum::EMPLOYEE]);

        $this->assertTrue($employee->isEmployee());
    }

    public function test_is_employee_returns_false_for_admin_role()
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);

        $this->assertFalse($admin->isEmployee());
    }

    public function test_get_roles_returns_all_available_roles()
    {
        $roles = User::getRoles();

        $this->assertIsArray($roles);
        $this->assertCount(2, $roles);
        $this->assertContains(UserRoleEnum::ADMIN->value, $roles);
        $this->assertContains(UserRoleEnum::EMPLOYEE->value, $roles);
    }

    public function test_user_password_is_hashed()
    {
        $user = User::factory()->create(['password' => 'plaintext']);

        $this->assertNotEquals('plaintext', $user->password);
        $this->assertTrue(password_verify('plaintext', $user->password));
    }

    public function test_user_can_be_soft_deleted()
    {
        $user = User::factory()->create();

        $user->delete();

        $this->assertSoftDeleted($user);
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_user_can_be_restored()
    {
        $user = User::factory()->create();
        $user->delete();

        $user->restore();

        $this->assertNotSoftDeleted($user);
    }

    public function test_user_can_be_force_deleted()
    {
        $user = User::factory()->create();

        $user->forceDelete();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_user_fillable_fields_are_mass_assignable()
    {
        $userData = [
            'name' => 'Maria Santos',
            'email' => 'maria@example.com',
            'cpf' => '98765432100',
            'password' => 'password456',
            'job_title' => 'Analista',
            'birth_date' => '1985-05-15',
            'postal_code' => '54321-987',
            'address' => 'Av Principal, 456',
            'role' => UserRoleEnum::ADMIN,
        ];

        $user = User::create($userData);

        foreach ($userData as $field => $value) {
            if ($field !== 'password') {
                if ($field === 'birth_date') {
                    $this->assertEquals($value, $user->$field->format('Y-m-d'));
                } else {
                    $this->assertEquals($value, $user->$field);
                }
            }
        }
    }

    public function test_user_hidden_fields_are_not_serialized()
    {
        $user = User::factory()->create();

        $userArray = $user->toArray();

        $this->assertArrayNotHasKey('password', $userArray);
        $this->assertArrayNotHasKey('remember_token', $userArray);
    }

    public function test_user_with_null_creator_returns_null()
    {
        $user = User::factory()->create(['created_by' => null]);

        $this->assertNull($user->creator);
    }

    public function test_user_without_created_users_returns_empty_collection()
    {
        $user = User::factory()->create();

        $this->assertCount(0, $user->createdUsers);
    }

    public function test_user_without_clock_ins_returns_empty_collection()
    {
        $user = User::factory()->create();

        $this->assertCount(0, $user->clockIns);
    }
} 