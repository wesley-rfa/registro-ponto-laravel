<?php

namespace Tests\Feature\Middleware;

use App\Enums\UserRoleEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class EnsureUserIsAdminTest extends TestCase
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
    }

    public function test_admin_user_can_access_protected_route(): void
    {
        $request = Request::create('/admin/protected-route', 'GET');
        $request->setUserResolver(fn() => $this->adminUser);
        
        $middleware = new \App\Http\Middleware\EnsureUserIsAdmin();
        
        $response = $middleware->handle($request, function ($request) {
            return response('Success', Response::HTTP_OK);
        });
        
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals('Success', $response->getContent());
    }

    public function test_employee_user_cannot_access_protected_route(): void
    {
        $request = Request::create('/admin/protected-route', 'GET');
        $request->setUserResolver(fn() => $this->employeeUser);
        
        $middleware = new \App\Http\Middleware\EnsureUserIsAdmin();
        
        $this->expectException(HttpException::class);
        
        $middleware->handle($request, function ($request) {
            return response('Success', Response::HTTP_OK);
        });
    }

    public function test_unauthenticated_user_cannot_access_protected_route(): void
    {
        $request = Request::create('/admin/protected-route', 'GET');
        $request->setUserResolver(fn() => null);
        
        $middleware = new \App\Http\Middleware\EnsureUserIsAdmin();
        
        $this->expectException(\ErrorException::class);
        
        $middleware->handle($request, function ($request) {
            return response('Success', Response::HTTP_OK);
        });
    }

    public function test_middleware_returns_forbidden_response_for_unauthorized_user(): void
    {
        $request = Request::create('/admin/protected-route', 'GET');
        $request->setUserResolver(fn() => $this->employeeUser);
        
        $middleware = new \App\Http\Middleware\EnsureUserIsAdmin();
        
        try {
            $middleware->handle($request, function ($request) {
                return response('Success', Response::HTTP_OK);
            });
        } catch (HttpException $e) {
            $this->assertEquals(Response::HTTP_FORBIDDEN, $e->getStatusCode());
            $this->assertEquals('Acesso não autorizado.', $e->getMessage());
        }
    }

    public function test_middleware_passes_request_to_next_handler_for_admin(): void
    {
        $request = Request::create('/admin/protected-route', 'POST', ['data' => 'test']);
        $request->setUserResolver(fn() => $this->adminUser);
        
        $middleware = new \App\Http\Middleware\EnsureUserIsAdmin();
        
        $response = $middleware->handle($request, function ($request) {
            return response('Processed: ' . $request->input('data'), Response::HTTP_OK);
        });
        
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals('Processed: test', $response->getContent());
    }
} 