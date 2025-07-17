<?php

namespace Tests\Unit\Repositories;

use App\Repositories\ClockInRepository;
use App\Models\ClockIn;
use App\Dtos\ClockIn\ListFilterDto;
use App\Dtos\ClockIn\CreateClockInDto;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Mockery;
use Tests\TestCase;
use Carbon\Carbon;

class ClockInRepositoryTest extends TestCase
{
    private $clockInModelMock;
    private ClockInRepository $clockInRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clockInModelMock = Mockery::mock(ClockIn::class);
        $this->clockInRepository = new ClockInRepository($this->clockInModelMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_returns_new_clock_in()
    {
        $dto = new CreateClockInDto(
            user_id: 1,
            registered_at: Carbon::now(),
        );
        $expectedClockIn = new ClockIn($dto->toArray());
        $expectedClockIn->id = 1;

        $this->clockInModelMock
            ->shouldReceive('where')
            ->once()
            ->with('user_id', $dto->user_id)
            ->andReturnSelf();
        $this->clockInModelMock
            ->shouldReceive('whereBetween')
            ->once()
            ->with('registered_at', Mockery::any())
            ->andReturnSelf();
        $this->clockInModelMock
            ->shouldReceive('first')
            ->once()
            ->andReturn(null);
        $this->clockInModelMock
            ->shouldReceive('create')
            ->once()
            ->with($dto->toArray())
            ->andReturn($expectedClockIn);

        $result = $this->clockInRepository->create($dto);

        $this->assertInstanceOf(ClockIn::class, $result);
        $this->assertSame($expectedClockIn, $result);
    }
} 