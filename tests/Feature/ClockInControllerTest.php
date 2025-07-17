<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\ClockInService;
use App\Dtos\ClockIn\ListFilterDto;
use App\Dtos\ClockIn\CreateClockInDto;
use App\Exceptions\DuplicateClockInException;
use App\Enums\UserRoleEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\Response;
use Mockery;
use Tests\TestCase;

class ClockInControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_index_returns_employee_view()
    {
        /** @var User $employee */
        $employee = User::factory()->create(['role' => UserRoleEnum::EMPLOYEE]);

        $response = $this->actingAs($employee)
            ->get(route('clock-in.index'));

        $response->assertStatus(Response::HTTP_OK);
        $response->assertViewIs('employee.index');
    }

    public function test_registers_returns_admin_registers_view_with_clock_ins()
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);
        $mockClockInService = Mockery::mock(ClockInService::class);
        
        $expectedClockIns = new LengthAwarePaginator(
            [
                [
                    'id' => 1,
                    'user_id' => 1,
                    'name' => 'Usuário 1',
                    'job_title' => 'Desenvolvedor',
                    'age' => 30,
                    'manager_name' => 'Gestor 1',
                    'registered_at' => now()
                ],
                [
                    'id' => 2,
                    'user_id' => 2,
                    'name' => 'Usuário 2',
                    'job_title' => 'Designer',
                    'age' => 25,
                    'manager_name' => 'Gestor 2',
                    'registered_at' => now()
                ],
            ],
            2,
            15
        );

        $mockClockInService->shouldReceive('findAll')
            ->once()
            ->with(Mockery::type(ListFilterDto::class))
            ->andReturn($expectedClockIns);

        $this->app->instance(ClockInService::class, $mockClockInService);

        $response = $this->actingAs($admin)
            ->get(route('admin.registers', [
                'start_date' => '2024-01-01',
                'end_date' => '2024-01-31'
            ]));

        $response->assertStatus(Response::HTTP_OK);
        $response->assertViewIs('admin.registers');
    }

    public function test_registers_without_date_filters()
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);
        $mockClockInService = Mockery::mock(ClockInService::class);
        
        $expectedClockIns = new LengthAwarePaginator([], 0, 15);

        $mockClockInService->shouldReceive('findAll')
            ->once()
            ->with(Mockery::type(ListFilterDto::class))
            ->andReturn($expectedClockIns);

        $this->app->instance(ClockInService::class, $mockClockInService);

        $response = $this->actingAs($admin)
            ->get(route('admin.registers'));

        $response->assertStatus(Response::HTTP_OK);
        $response->assertViewIs('admin.registers');
        $response->assertViewHas('clockIns', $expectedClockIns);
    }

    public function test_store_creates_clock_in_successfully()
    {
        /** @var User $employee */
        $employee = User::factory()->create(['role' => UserRoleEnum::EMPLOYEE]);
        $mockClockInService = Mockery::mock(ClockInService::class);

        $mockClockInService->shouldReceive('create')
            ->once()
            ->with(Mockery::type(CreateClockInDto::class));

        $this->app->instance(ClockInService::class, $mockClockInService);

        $response = $this->actingAs($employee)
            ->post(route('clock-in.store'));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Ponto registrado com sucesso!');
    }

    public function test_store_handles_duplicate_clock_in_exception()
    {
        /** @var User $employee */
        $employee = User::factory()->create(['role' => UserRoleEnum::EMPLOYEE]);
        $mockClockInService = Mockery::mock(ClockInService::class);

        $mockClockInService->shouldReceive('create')
            ->once()
            ->with(Mockery::type(CreateClockInDto::class))
            ->andThrow(new DuplicateClockInException('Ponto já registrado para hoje'));

        $this->app->instance(ClockInService::class, $mockClockInService);

        $response = $this->actingAs($employee)
            ->post(route('clock-in.store'));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Ponto já registrado para hoje');
    }

    public function test_store_handles_generic_exception()
    {
        /** @var User $employee */
        $employee = User::factory()->create(['role' => UserRoleEnum::EMPLOYEE]);
        $mockClockInService = Mockery::mock(ClockInService::class);

        $mockClockInService->shouldReceive('create')
            ->once()
            ->with(Mockery::type(CreateClockInDto::class))
            ->andThrow(new \Exception('Erro interno do servidor'));

        $this->app->instance(ClockInService::class, $mockClockInService);

        $response = $this->actingAs($employee)
            ->post(route('clock-in.store'));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Erro ao registrar ponto: Erro interno do servidor');
    }

    public function test_store_requires_authentication()
    {
        $response = $this->post(route('clock-in.store'));

        $response->assertRedirect(route('login'));
    }

    public function test_registers_requires_authentication()
    {
        $response = $this->get(route('admin.registers'));

        $response->assertRedirect(route('login'));
    }

    public function test_index_requires_authentication()
    {
        $response = $this->get(route('clock-in.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_registers_with_invalid_date_range()
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);

        $response = $this->actingAs($admin)
            ->get(route('admin.registers', [
                'start_date' => '2024-01-31',
                'end_date' => '2024-01-01'
            ]));

        $response->assertSessionHasErrors(['end_date']);
    }

    public function test_registers_with_invalid_date_format()
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);

        $response = $this->actingAs($admin)
            ->get(route('admin.registers', [
                'start_date' => 'invalid-date',
                'end_date' => '2024-01-31'
            ]));

        $response->assertSessionHasErrors(['start_date']);
    }
} 