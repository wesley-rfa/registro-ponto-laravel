<?php

namespace Tests\Unit\Services;

use App\Services\ClockInService;
use App\Repositories\Interfaces\ClockInRepositoryInterface;
use App\Dtos\ClockIn\CreateClockInDto;
use App\Dtos\ClockIn\ListFilterDto;
use App\Models\ClockIn;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Tests\TestCase;
use Carbon\Carbon;

/**
 * @mixin \Mockery\MockInterface
 */
class ClockInServiceTest extends TestCase
{
    /** @var \Mockery\MockInterface&\App\Repositories\Interfaces\ClockInRepositoryInterface */
    private $mockRepository;
    private ClockInService $clockInService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockRepository = Mockery::mock(ClockInRepositoryInterface::class);
        $this->clockInService = new ClockInService($this->mockRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_find_all_returns_paginator()
    {
        $dto = new ListFilterDto(
            startDate: Carbon::parse('2024-01-01'),
            endDate: Carbon::parse('2024-01-31')
        );
        
        $expectedPaginator = new LengthAwarePaginator([], 0, 15);
        
        $this->mockRepository
            ->shouldReceive('findAll')
            ->once()
            ->with($dto)
            ->andReturn($expectedPaginator);

        $result = $this->clockInService->findAll($dto);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertSame($expectedPaginator, $result);
    }

    public function test_find_all_with_null_dates()
    {
        $dto = new ListFilterDto(
            startDate: null,
            endDate: null
        );
        
        $expectedPaginator = new LengthAwarePaginator([], 0, 15);
        
        $this->mockRepository
            ->shouldReceive('findAll')
            ->once()
            ->with($dto)
            ->andReturn($expectedPaginator);

        $result = $this->clockInService->findAll($dto);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertSame($expectedPaginator, $result);
    }

    public function test_create_returns_new_clock_in()
    {
        $registeredAt = Carbon::now();
        $dto = new CreateClockInDto(
            user_id: 1,
            registered_at: $registeredAt
        );

        $expectedClockIn = new ClockIn([
            'user_id' => 1,
            'registered_at' => $registeredAt
        ]);
        $expectedClockIn->id = 1;

        $this->mockRepository
            ->shouldReceive('create')
            ->once()
            ->with($dto)
            ->andReturn($expectedClockIn);

        $result = $this->clockInService->create($dto);

        $this->assertInstanceOf(ClockIn::class, $result);
        $this->assertSame($expectedClockIn, $result);
        $this->assertEquals(1, $result->user_id);
        $this->assertEquals($registeredAt->toDateTimeString(), $result->registered_at->toDateTimeString());
    }

    public function test_create_with_different_user_id()
    {
        $registeredAt = Carbon::parse('2024-01-15 08:30:00');
        $dto = new CreateClockInDto(
            user_id: 5,
            registered_at: $registeredAt
        );

        $expectedClockIn = new ClockIn([
            'user_id' => 5,
            'registered_at' => $registeredAt
        ]);
        $expectedClockIn->id = 10;

        $this->mockRepository
            ->shouldReceive('create')
            ->once()
            ->with($dto)
            ->andReturn($expectedClockIn);

        $result = $this->clockInService->create($dto);

        $this->assertInstanceOf(ClockIn::class, $result);
        $this->assertSame($expectedClockIn, $result);
        $this->assertEquals(5, $result->user_id);
        $this->assertEquals($registeredAt, $result->registered_at);
        $this->assertEquals(10, $result->id);
    }

    public function test_service_handles_repository_exceptions()
    {
        $dto = new CreateClockInDto(
            user_id: 1,
            registered_at: Carbon::now()
        );

        $this->mockRepository
            ->shouldReceive('create')
            ->once()
            ->with($dto)
            ->andThrow(new \Exception('Erro no repositório'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Erro no repositório');

        $this->clockInService->create($dto);
    }

    public function test_find_all_handles_repository_exceptions()
    {
        $dto = new ListFilterDto(
            startDate: Carbon::parse('2024-01-01'),
            endDate: Carbon::parse('2024-01-31')
        );

        $this->mockRepository
            ->shouldReceive('findAll')
            ->once()
            ->with($dto)
            ->andThrow(new \Exception('Erro ao buscar registros'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Erro ao buscar registros');

        $this->clockInService->findAll($dto);
    }
} 