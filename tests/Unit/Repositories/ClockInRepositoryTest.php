<?php

namespace Tests\Unit\Repositories;

use App\Repositories\ClockInRepository;
use App\Models\ClockIn;
use App\Dtos\ClockIn\ListFilterDto;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;
use Carbon\Carbon;

class ClockInRepositoryTest extends TestCase
{
    private $mockModel;
    private ClockInRepository $clockInRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockModel = Mockery::mock(ClockIn::class);
        $this->clockInRepository = new ClockInRepository($this->mockModel);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_find_all_returns_paginator_with_mock_data()
    {
        $mockResults = [
            (object) [
                'id' => 1,
                'name' => 'João Silva',
                'job_title' => 'Desenvolvedor',
                'age' => 30,
                'manager_name' => 'Maria Santos',
                'registered_at' => '17/07/2025 10:30:00'
            ],
            (object) [
                'id' => 2,
                'name' => 'Pedro Costa',
                'job_title' => 'Analista',
                'age' => 28,
                'manager_name' => 'Maria Santos',
                'registered_at' => '17/07/2025 09:15:00'
            ]
        ];
        DB::shouldReceive('selectOne')
            ->once()
            ->andReturn((object) ['total' => 2]);

        DB::shouldReceive('select')
            ->once()
            ->andReturn($mockResults);

        $dto = new ListFilterDto(startDate: null, endDate: null);
        $result = $this->clockInRepository->findAll($dto);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(2, $result->total());
        $this->assertEquals(15, $result->perPage());
        $this->assertCount(2, $result->items());

        $firstItem = $result->items()[0];
        $this->assertEquals(1, $firstItem['id']);
        $this->assertEquals('João Silva', $firstItem['name']);
        $this->assertEquals('Desenvolvedor', $firstItem['job_title']);
        $this->assertEquals(30, $firstItem['age']);
        $this->assertEquals('Maria Santos', $firstItem['manager_name']);
        $this->assertEquals('17/07/2025 10:30:00', $firstItem['registered_at']);
    }

    public function test_find_all_with_start_date_filter()
    {
        $mockResults = [
            (object) [
                'id' => 1,
                'name' => 'João Silva',
                'job_title' => 'Desenvolvedor',
                'age' => 30,
                'manager_name' => 'Maria Santos',
                'registered_at' => '17/07/2025 10:30:00'
            ]
        ];

        DB::shouldReceive('selectOne')
            ->once()
            ->andReturn((object) ['total' => 1]);

        DB::shouldReceive('select')
            ->once()
            ->andReturn($mockResults);

        $startDate = Carbon::now()->subDays(3);
        $dto = new ListFilterDto(startDate: $startDate, endDate: null);
        $result = $this->clockInRepository->findAll($dto);

        $this->assertEquals(1, $result->total());
    }

    public function test_find_all_with_end_date_filter()
    {
        $mockResults = [
            (object) [
                'id' => 1,
                'name' => 'João Silva',
                'job_title' => 'Desenvolvedor',
                'age' => 30,
                'manager_name' => 'Maria Santos',
                'registered_at' => '17/07/2025 10:30:00'
            ]
        ];

        DB::shouldReceive('selectOne')
            ->once()
            ->andReturn((object) ['total' => 1]);

        DB::shouldReceive('select')
            ->once()
            ->andReturn($mockResults);

        $endDate = Carbon::now()->subDays(1);
        $dto = new ListFilterDto(startDate: null, endDate: $endDate);
        $result = $this->clockInRepository->findAll($dto);

        $this->assertEquals(1, $result->total());
    }

    public function test_find_all_with_both_date_filters()
    {
        $mockResults = [
            (object) [
                'id' => 1,
                'name' => 'João Silva',
                'job_title' => 'Desenvolvedor',
                'age' => 30,
                'manager_name' => 'Maria Santos',
                'registered_at' => '17/07/2025 10:30:00'
            ]
        ];

        DB::shouldReceive('selectOne')
            ->once()
            ->andReturn((object) ['total' => 1]);

        DB::shouldReceive('select')
            ->once()
            ->andReturn($mockResults);

        $startDate = Carbon::now()->subDays(4);
        $endDate = Carbon::now()->subDays(2);
        $dto = new ListFilterDto(startDate: $startDate, endDate: $endDate);
        $result = $this->clockInRepository->findAll($dto);

        $this->assertEquals(1, $result->total());
    }

    public function test_find_all_returns_empty_paginator_when_no_results()
    {
        DB::shouldReceive('selectOne')
            ->once()
            ->andReturn((object) ['total' => 0]);

        DB::shouldReceive('select')
            ->once()
            ->andReturn([]);

        $dto = new ListFilterDto(startDate: null, endDate: null);
        $result = $this->clockInRepository->findAll($dto);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(0, $result->total());
        $this->assertCount(0, $result->items());
    }
}
