<?php

namespace Tests\Feature;

use App\Models\User;
use App\Enums\UserRoleEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Http\Response;

class UserDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_delete_user()
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);
        
        /** @var User $employee */
        $employee = User::factory()->create(['role' => UserRoleEnum::EMPLOYEE]);

        $response = $this->actingAs($admin)
            ->delete(route('admin.users.destroy'), [
                'user_id' => $employee->id
            ]);

        $response->assertRedirect(route('admin.users'))
            ->assertSessionHas('success', 'Usuário excluído com sucesso!');

        $this->assertSoftDeleted($employee);
    }

    public function test_employee_cannot_delete_user()
    {
        /** @var User $employee */
        $employee = User::factory()->create(['role' => UserRoleEnum::EMPLOYEE]);
        
        /** @var User $otherEmployee */
        $otherEmployee = User::factory()->create(['role' => UserRoleEnum::EMPLOYEE]);

        $response = $this->actingAs($employee)
            ->delete(route('admin.users.destroy'), [
                'user_id' => $otherEmployee->id
            ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_admin_cannot_delete_another_admin()
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);
        
        /** @var User $otherAdmin */
        $otherAdmin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);

        $response = $this->actingAs($admin)
            ->delete(route('admin.users.destroy'), [
                'user_id' => $otherAdmin->id
            ]);

        $response->assertSessionHasErrors(['user_id']);
    }

    public function test_admin_cannot_delete_nonexistent_user()
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);

        $response = $this->actingAs($admin)
            ->delete(route('admin.users.destroy'), [
                'user_id' => 999999
            ]);

        $response->assertSessionHasErrors(['user_id']);
    }

    public function test_unauthenticated_user_cannot_delete_user()
    {
        /** @var User $employee */
        $employee = User::factory()->create(['role' => UserRoleEnum::EMPLOYEE]);

        $response = $this->delete(route('admin.users.destroy'), [
            'user_id' => $employee->id
        ]);

        $response->assertRedirect('/login');
    }

    public function test_delete_user_without_user_id_validation()
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);

        $response = $this->actingAs($admin)
            ->delete(route('admin.users.destroy'), []);

        $response->assertSessionHasErrors(['user_id']);
    }

    public function test_delete_user_with_invalid_user_id_type()
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);

        $response = $this->actingAs($admin)
            ->delete(route('admin.users.destroy'), [
                'user_id' => 'invalid'
            ]);

        $response->assertSessionHasErrors(['user_id']);
    }
} 