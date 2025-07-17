<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Http\Response;

class CepSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_search_cep()
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->getJson('/users/search-cep?cep=01001000');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'cep',
                'address'
            ]);
    }

    public function test_returns_404_for_invalid_cep()
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->getJson('/users/search-cep?cep=00000000');

        $response->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJson([
                'success' => false,
                'message' => 'CEP não encontrado'
            ]);
    }

    public function test_non_admin_cannot_search_cep()
    {
        /** @var User $employee */
        $employee = User::factory()->create(['role' => 'employee']);

        $response = $this->actingAs($employee)
            ->getJson('/users/search-cep?cep=01001000');

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_unauthenticated_user_cannot_search_cep()
    {
        $response = $this->getJson('/users/search-cep?cep=01001000');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_validates_cep_format()
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->getJson('/users/search-cep?cep=invalid');

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_requires_cep_parameter()
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->getJson('/users/search-cep');

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }
} 