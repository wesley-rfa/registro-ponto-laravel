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
            ->delete(route('admin.users.destroy', $employee->id));

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
            ->delete(route('admin.users.destroy', $otherEmployee->id));

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_admin_cannot_delete_another_admin()
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);
        
        /** @var User $otherAdmin */
        $otherAdmin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);

        $response = $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $otherAdmin->id));

        $response->assertRedirect(route('admin.users'))
            ->assertSessionHas('error', 'Não é possível excluir outro administrador.');
    }

    public function test_admin_cannot_delete_nonexistent_user()
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);

        $response = $this->actingAs($admin)
            ->delete(route('admin.users.destroy', 999999));

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_unauthenticated_user_cannot_delete_user()
    {
        /** @var User $employee */
        $employee = User::factory()->create(['role' => UserRoleEnum::EMPLOYEE]);

        $response = $this->delete(route('admin.users.destroy', $employee->id));

        $response->assertRedirect('/login');
    }

    public function test_delete_user_with_invalid_user_id_type()
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);

        $response = $this->actingAs($admin)
            ->delete(route('admin.users.destroy', 'invalid'));

        $response->assertStatus(404);
    }
} 