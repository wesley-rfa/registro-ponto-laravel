<?php

namespace Tests\Feature\Repositories;

use App\Repositories\ClockInRepository;
use App\Models\ClockIn;
use App\Models\User;
use App\Dtos\ClockIn\CreateClockInDto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use App\Enums\UserRoleEnum;

class ClockInRepositoryIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private ClockInRepository $clockInRepository;
    private User $user;
    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clockInRepository = new ClockInRepository(new ClockIn());
        $this->manager = User::factory()->create(['role' => UserRoleEnum::ADMIN->value]);
        $this->user = User::factory()->create(['created_by' => $this->manager->id]);
    }

    public function test_create_returns_new_clock_in_with_current_time()
    {
        $now = Carbon::now();
        $dto = new CreateClockInDto(
            user_id: $this->user->id,
            registered_at: $now,
        );

        $result = $this->clockInRepository->create($dto);

        $this->assertInstanceOf(ClockIn::class, $result);
        $this->assertEquals($this->user->id, $result->user_id);
        $this->assertEquals($now->toDateTimeString(), $result->registered_at->toDateTimeString());
        $this->assertDatabaseHas('clock_ins', [
            'user_id' => $this->user->id,
        ]);
    }

    public function test_create_returns_new_clock_in_with_specific_time()
    {
        $specificTime = Carbon::create(2024, 1, 15, 9, 30, 0);
        $dto = new CreateClockInDto(
            user_id: $this->user->id,
            registered_at: $specificTime,
        );

        $result = $this->clockInRepository->create($dto);

        $this->assertInstanceOf(ClockIn::class, $result);
        $this->assertEquals($this->user->id, $result->user_id);
        $this->assertEquals($specificTime->toDateTimeString(), $result->registered_at->toDateTimeString());
    }

    public function test_create_throws_exception_for_duplicate_clock_in_same_second()
    {
        $now = Carbon::now();
        
        $dto1 = new CreateClockInDto(
            user_id: $this->user->id,
            registered_at: $now,
        );
        
        $dto2 = new CreateClockInDto(
            user_id: $this->user->id,
            registered_at: $now,
        );

        $this->clockInRepository->create($dto1);

        $this->expectException(\App\Exceptions\DuplicateClockInException::class);
        $this->clockInRepository->create($dto2);
    }

    public function test_create_allows_clock_in_different_seconds()
    {
        $time1 = Carbon::now();
        $time2 = $time1->copy()->addSecond();
        
        $dto1 = new CreateClockInDto(
            user_id: $this->user->id,
            registered_at: $time1,
        );
        
        $dto2 = new CreateClockInDto(
            user_id: $this->user->id,
            registered_at: $time2,
        );

        $result1 = $this->clockInRepository->create($dto1);
        $result2 = $this->clockInRepository->create($dto2);

        $this->assertInstanceOf(ClockIn::class, $result1);
        $this->assertInstanceOf(ClockIn::class, $result2);
        $this->assertEquals($time1->toDateTimeString(), $result1->registered_at->toDateTimeString());
        $this->assertEquals($time2->toDateTimeString(), $result2->registered_at->toDateTimeString());
    }

    public function test_create_allows_clock_in_different_users_same_time()
    {
        $otherUser = User::factory()->create(['created_by' => $this->manager->id]);
        $now = Carbon::now();
        
        $dto1 = new CreateClockInDto(
            user_id: $this->user->id,
            registered_at: $now,
        );
        
        $dto2 = new CreateClockInDto(
            user_id: $otherUser->id,
            registered_at: $now,
        );

        $result1 = $this->clockInRepository->create($dto1);
        $result2 = $this->clockInRepository->create($dto2);

        $this->assertInstanceOf(ClockIn::class, $result1);
        $this->assertInstanceOf(ClockIn::class, $result2);
        $this->assertEquals($this->user->id, $result1->user_id);
        $this->assertEquals($otherUser->id, $result2->user_id);
    }
} 