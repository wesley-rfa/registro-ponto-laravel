<?php

namespace Tests\Unit\Dtos;

use App\Dtos\ClockIn\CreateClockInDto;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Tests\TestCase;

class CreateClockInDtoTest extends TestCase
{
    public function test_creates_dto_with_properties()
    {
        $userId = 456;
        $registeredAt = Carbon::parse('2024-03-15 08:30:00');
        
        $dto = new CreateClockInDto(
            user_id: $userId,
            registered_at: $registeredAt
        );

        $this->assertEquals($userId, $dto->user_id);
        $this->assertEquals($registeredAt, $dto->registered_at);
    }

    public function test_create_uses_auth_id_and_current_time()
    {
        $expectedUserId = 789;
        Auth::shouldReceive('id')->once()->andReturn($expectedUserId);
        
        $beforeCreation = Carbon::now();
        $dto = CreateClockInDto::create();
        $afterCreation = Carbon::now();

        $this->assertEquals($expectedUserId, $dto->user_id);
        $this->assertGreaterThanOrEqual($beforeCreation, $dto->registered_at);
        $this->assertLessThanOrEqual($afterCreation, $dto->registered_at);
    }

    public function test_to_array_returns_correct_structure()
    {
        $userId = 234;
        $registeredAt = Carbon::parse('2024-01-20 14:45:30');
        
        $dto = new CreateClockInDto(
            user_id: $userId,
            registered_at: $registeredAt
        );

        $array = $dto->toArray();

        $expected = [
            'user_id' => $userId,
            'registered_at' => $registeredAt,
        ];

        $this->assertEquals($expected, $array);
    }

    public function test_to_array_returns_carbon_instance_for_registered_at()
    {
        $registeredAt = Carbon::parse('2024-02-10 16:20:15');
        
        $dto = new CreateClockInDto(
            user_id: 567,
            registered_at: $registeredAt
        );

        $array = $dto->toArray();

        $this->assertInstanceOf(Carbon::class, $array['registered_at']);
        $this->assertEquals($registeredAt, $array['registered_at']);
    }

    public function test_properties_are_readonly()
    {
        $dto = new CreateClockInDto(
            user_id: 890,
            registered_at: Carbon::now()
        );

        $this->assertTrue(property_exists($dto, 'user_id'));
        $this->assertTrue(property_exists($dto, 'registered_at'));
        
        $this->assertEquals(890, $dto->user_id);
        $this->assertInstanceOf(Carbon::class, $dto->registered_at);
    }
} 