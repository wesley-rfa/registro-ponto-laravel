<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Http\Response;

class UserShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_user_details()
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        
        /** @var User $user */
        $user = User::factory()->create([
            'name' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'cpf' => '52998224725',
            'job_title' => 'Desenvolvedor',
            'birth_date' => '1990-01-01',
            'postal_code' => '01001-000',
            'address' => 'Rua das Flores, 123'
        ]);

        $response = $this->actingAs($admin)
            ->getJson(route('admin.users.show', $user));

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'id' => $user->id,
                'name' => 'João Silva',
                'email' => 'joao@exemplo.com',
                'cpf' => '529.982.247-25',
                'job_title' => 'Desenvolvedor',
                'birth_date' => '1990-01-01',
                'postal_code' => '01001-000',
                'address' => 'Rua das Flores, 123',
                'role' => 'employee'
            ]);
    }

    public function test_returns_404_for_nonexistent_user()
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->getJson('/users/999');

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_non_admin_cannot_access_user_details()
    {
        /** @var User $employee */
        $employee = User::factory()->create(['role' => 'employee']);
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($employee)
            ->getJson(route('admin.users.show', $user));

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_unauthenticated_user_cannot_access_user_details()
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->getJson(route('admin.users.show', $user));

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }
} 