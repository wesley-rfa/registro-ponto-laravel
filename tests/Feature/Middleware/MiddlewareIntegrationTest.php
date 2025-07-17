<?php

namespace Tests\Feature\Middleware;

use App\Enums\UserRoleEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class MiddlewareIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private User $employeeUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->adminUser = User::factory()->create([
            'role' => UserRoleEnum::ADMIN
        ]);
        
        $this->employeeUser = User::factory()->create([
            'role' => UserRoleEnum::EMPLOYEE
        ]);

        Route::middleware(['auth', 'admin'])->group(function () {
            Route::get('/admin/test', function () {
                return response('Admin route accessed', Response::HTTP_OK);
            });
        });

        Route::middleware(['auth', 'employee'])->group(function () {
            Route::get('/employee/test', function () {
                return response('Employee route accessed', Response::HTTP_OK);
            });
        });
    }

    public function test_admin_can_access_admin_route(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/test');

        $response->assertStatus(Response::HTTP_OK);
        $response->assertSee('Admin route accessed');
    }

    public function test_employee_cannot_access_admin_route(): void
    {
        $response = $this->actingAs($this->employeeUser)
            ->get('/admin/test');

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_employee_can_access_employee_route(): void
    {
        $response = $this->actingAs($this->employeeUser)
            ->get('/employee/test');

        $response->assertStatus(Response::HTTP_OK);
        $response->assertSee('Employee route accessed');
    }

    public function test_admin_cannot_access_employee_route(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/employee/test');

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_unauthenticated_user_cannot_access_admin_route(): void
    {
        $response = $this->get('/admin/test');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_unauthenticated_user_cannot_access_employee_route(): void
    {
        $response = $this->get('/employee/test');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }
} 