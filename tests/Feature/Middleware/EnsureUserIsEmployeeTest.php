<?php

namespace Tests\Feature\Middleware;

use App\Enums\UserRoleEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class EnsureUserIsEmployeeTest extends TestCase
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

    public function test_employee_user_can_access_protected_route(): void
    {
        $request = Request::create('/employee/protected-route', 'GET');
        $request->setUserResolver(fn() => $this->employeeUser);
        
        $middleware = new \App\Http\Middleware\EnsureUserIsEmployee();
        
        $response = $middleware->handle($request, function ($request) {
            return response('Success', Response::HTTP_OK);
        });
        
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals('Success', $response->getContent());
    }

    public function test_admin_user_cannot_access_protected_route(): void
    {
        $request = Request::create('/employee/protected-route', 'GET');
        $request->setUserResolver(fn() => $this->adminUser);
        
        $middleware = new \App\Http\Middleware\EnsureUserIsEmployee();
        
        $this->expectException(HttpException::class);
        
        $middleware->handle($request, function ($request) {
            return response('Success', Response::HTTP_OK);
        });
    }

    public function test_unauthenticated_user_cannot_access_protected_route(): void
    {
        $request = Request::create('/employee/protected-route', 'GET');
        $request->setUserResolver(fn() => null);
        
        $middleware = new \App\Http\Middleware\EnsureUserIsEmployee();
        
        $this->expectException(\ErrorException::class);
        
        $middleware->handle($request, function ($request) {
            return response('Success', Response::HTTP_OK);
        });
    }

    public function test_middleware_returns_forbidden_response_for_unauthorized_user(): void
    {
        $request = Request::create('/employee/protected-route', 'GET');
        $request->setUserResolver(fn() => $this->adminUser);
        
        $middleware = new \App\Http\Middleware\EnsureUserIsEmployee();
        
        try {
            $middleware->handle($request, function ($request) {
                return response('Success', Response::HTTP_OK);
            });
        } catch (HttpException $e) {
            $this->assertEquals(Response::HTTP_FORBIDDEN, $e->getStatusCode());
            $this->assertEquals('Acesso não autorizado.', $e->getMessage());
        }
    }

    public function test_middleware_passes_request_to_next_handler_for_employee(): void
    {
        $request = Request::create('/employee/protected-route', 'POST', ['data' => 'test']);
        $request->setUserResolver(fn() => $this->employeeUser);
        
        $middleware = new \App\Http\Middleware\EnsureUserIsEmployee();
        
        $response = $middleware->handle($request, function ($request) {
            return response('Processed: ' . $request->input('data'), Response::HTTP_OK);
        });
        
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals('Processed: test', $response->getContent());
    }

    public function test_middleware_handles_different_http_methods_for_employee(): void
    {
        $methods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'];
        
        foreach ($methods as $method) {
            $request = Request::create('/employee/protected-route', $method);
            $request->setUserResolver(fn() => $this->employeeUser);
            
            $middleware = new \App\Http\Middleware\EnsureUserIsEmployee();
            
            $response = $middleware->handle($request, function ($request) use ($method) {
                return response("Method: {$method}", Response::HTTP_OK);
            });
            
            $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
            $this->assertEquals("Method: {$method}", $response->getContent());
        }
    }

    public function test_middleware_handles_request_with_headers_for_employee(): void
    {
        $request = Request::create('/employee/protected-route', 'GET');
        $request->setUserResolver(fn() => $this->employeeUser);
        $request->headers->set('X-Custom-Header', 'test-value');
        
        $middleware = new \App\Http\Middleware\EnsureUserIsEmployee();
        
        $response = $middleware->handle($request, function ($request) {
            return response('Header: ' . $request->header('X-Custom-Header'), Response::HTTP_OK);
        });
        
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals('Header: test-value', $response->getContent());
    }
} 