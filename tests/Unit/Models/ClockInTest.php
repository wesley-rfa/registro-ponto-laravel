<?php

namespace Tests\Unit\Models;

use App\Models\ClockIn;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClockInTest extends TestCase
{
    use RefreshDatabase;

    public function test_clock_in_can_be_created_with_valid_data()
    {
        $user = User::factory()->create();
        $clockInData = [
            'user_id' => $user->id,
            'registered_at' => '2024-01-15 08:30:00',
        ];

        $clockIn = ClockIn::create($clockInData);

        $this->assertInstanceOf(ClockIn::class, $clockIn);
        $this->assertEquals($user->id, $clockIn->user_id);
        $this->assertEquals('2024-01-15 08:30:00', $clockIn->registered_at->format('Y-m-d H:i:s'));
    }

    public function test_clock_in_casts_registered_at_to_datetime()
    {
        $user = User::factory()->create();
        $clockIn = ClockIn::factory()->create([
            'user_id' => $user->id,
            'registered_at' => '2024-01-15 08:30:00',
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $clockIn->registered_at);
        $this->assertEquals('2024-01-15 08:30:00', $clockIn->registered_at->format('Y-m-d H:i:s'));
    }

    public function test_clock_in_has_user_relationship()
    {
        $user = User::factory()->create();
        $clockIn = ClockIn::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $clockIn->user);
        $this->assertEquals($user->id, $clockIn->user->id);
    }

    public function test_scope_in_date_range_filters_by_date_range()
    {
        $user = User::factory()->create();
        
        $clockIn1 = ClockIn::factory()->create([
            'user_id' => $user->id,
            'registered_at' => '2024-01-15 08:00:00',
        ]);
        
        $clockIn2 = ClockIn::factory()->create([
            'user_id' => $user->id,
            'registered_at' => '2024-01-16 08:00:00',
        ]);
        
        $clockIn3 = ClockIn::factory()->create([
            'user_id' => $user->id,
            'registered_at' => '2024-01-17 08:00:00',
        ]);

        $startDate = '2024-01-15 00:00:00';
        $endDate = '2024-01-16 23:59:59';

        $filteredClockIns = ClockIn::inDateRange($startDate, $endDate)->get();

        $this->assertCount(2, $filteredClockIns);
        $this->assertTrue($filteredClockIns->contains($clockIn1));
        $this->assertTrue($filteredClockIns->contains($clockIn2));
        $this->assertFalse($filteredClockIns->contains($clockIn3));
    }

    public function test_scope_in_date_range_with_exact_dates()
    {
        $user = User::factory()->create();
        
        $clockIn = ClockIn::factory()->create([
            'user_id' => $user->id,
            'registered_at' => '2024-01-15 12:00:00',
        ]);

        $startDate = '2024-01-15 12:00:00';
        $endDate = '2024-01-15 12:00:00';

        $filteredClockIns = ClockIn::inDateRange($startDate, $endDate)->get();

        $this->assertCount(1, $filteredClockIns);
        $this->assertTrue($filteredClockIns->contains($clockIn));
    }

    public function test_scope_in_date_range_with_empty_range()
    {
        $user = User::factory()->create();
        
        ClockIn::factory()->create([
            'user_id' => $user->id,
            'registered_at' => '2024-01-15 08:00:00',
        ]);

        $startDate = '2024-01-20 00:00:00';
        $endDate = '2024-01-25 23:59:59';

        $filteredClockIns = ClockIn::inDateRange($startDate, $endDate)->get();

        $this->assertCount(0, $filteredClockIns);
    }

    public function test_scope_in_date_range_with_reversed_dates()
    {
        $user = User::factory()->create();
        
        $clockIn = ClockIn::factory()->create([
            'user_id' => $user->id,
            'registered_at' => '2024-01-15 12:00:00',
        ]);

        $startDate = '2024-01-16 00:00:00';
        $endDate = '2024-01-15 23:59:59';

        $filteredClockIns = ClockIn::inDateRange($startDate, $endDate)->get();

        $this->assertCount(0, $filteredClockIns);
    }

    public function test_clock_in_can_be_soft_deleted()
    {
        $user = User::factory()->create();
        $clockIn = ClockIn::factory()->create(['user_id' => $user->id]);

        $clockIn->delete();

        $this->assertSoftDeleted($clockIn);
        $this->assertDatabaseHas('clock_ins', ['id' => $clockIn->id]);
    }

    public function test_clock_in_can_be_restored()
    {
        $user = User::factory()->create();
        $clockIn = ClockIn::factory()->create(['user_id' => $user->id]);
        $clockIn->delete();

        $clockIn->restore();

        $this->assertNotSoftDeleted($clockIn);
    }

    public function test_clock_in_can_be_force_deleted()
    {
        $user = User::factory()->create();
        $clockIn = ClockIn::factory()->create(['user_id' => $user->id]);

        $clockIn->forceDelete();

        $this->assertDatabaseMissing('clock_ins', ['id' => $clockIn->id]);
    }

    public function test_clock_in_fillable_fields_are_mass_assignable()
    {
        $user = User::factory()->create();
        $clockInData = [
            'user_id' => $user->id,
            'registered_at' => '2024-01-15 08:30:00',
        ];

        $clockIn = ClockIn::create($clockInData);

        $this->assertEquals($user->id, $clockIn->user_id);
        $this->assertEquals('2024-01-15 08:30:00', $clockIn->registered_at->format('Y-m-d H:i:s'));
    }

    public function test_clock_in_with_different_time_formats()
    {
        $user = User::factory()->create();
        
        $clockIn1 = ClockIn::factory()->create([
            'user_id' => $user->id,
            'registered_at' => '2024-01-15 08:30:00',
        ]);
        
        $clockIn2 = ClockIn::factory()->create([
            'user_id' => $user->id,
            'registered_at' => '2024-01-15 17:45:30',
        ]);
        
        $clockIn3 = ClockIn::factory()->create([
            'user_id' => $user->id,
            'registered_at' => '2024-01-15 23:59:59',
        ]);

        $this->assertEquals('08:30:00', $clockIn1->registered_at->format('H:i:s'));
        $this->assertEquals('17:45:30', $clockIn2->registered_at->format('H:i:s'));
        $this->assertEquals('23:59:59', $clockIn3->registered_at->format('H:i:s'));
    }

    public function test_clock_in_scope_with_carbon_instances()
    {
        $user = User::factory()->create();
        
        $clockIn = ClockIn::factory()->create([
            'user_id' => $user->id,
            'registered_at' => '2024-01-15 12:00:00',
        ]);

        $startDate = \Carbon\Carbon::parse('2024-01-15 00:00:00');
        $endDate = \Carbon\Carbon::parse('2024-01-15 23:59:59');

        $filteredClockIns = ClockIn::inDateRange($startDate, $endDate)->get();

        $this->assertCount(1, $filteredClockIns);
        $this->assertTrue($filteredClockIns->contains($clockIn));
    }

    public function test_clock_in_scope_with_string_dates()
    {
        $user = User::factory()->create();
        
        $clockIn = ClockIn::factory()->create([
            'user_id' => $user->id,
            'registered_at' => '2024-01-15 12:00:00',
        ]);

        $startDate = '2024-01-15 00:00:00';
        $endDate = '2024-01-15 23:59:59';

        $filteredClockIns = ClockIn::inDateRange($startDate, $endDate)->get();

        $this->assertCount(1, $filteredClockIns);
        $this->assertTrue($filteredClockIns->contains($clockIn));
    }

    public function test_clock_in_scope_with_null_dates()
    {
        $user = User::factory()->create();
        
        ClockIn::factory()->create([
            'user_id' => $user->id,
            'registered_at' => '2024-01-15 12:00:00',
        ]);

        $filteredClockIns = ClockIn::inDateRange(null, null)->get();

        $this->assertCount(0, $filteredClockIns);
    }

    public function test_clock_in_scope_with_empty_string_dates()
    {
        $user = User::factory()->create();
        
        ClockIn::factory()->create([
            'user_id' => $user->id,
            'registered_at' => '2024-01-15 12:00:00',
        ]);

        $filteredClockIns = ClockIn::inDateRange('', '')->get();

        $this->assertCount(0, $filteredClockIns);
    }

    public function test_clock_in_scope_with_invalid_date_format()
    {
        $user = User::factory()->create();
        
        ClockIn::factory()->create([
            'user_id' => $user->id,
            'registered_at' => '2024-01-15 12:00:00',
        ]);

        $startDate = 'invalid-date';
        $endDate = 'invalid-date';

        $filteredClockIns = ClockIn::inDateRange($startDate, $endDate)->get();

        $this->assertCount(0, $filteredClockIns);
    }

    public function test_clock_in_scope_with_future_dates()
    {
        $user = User::factory()->create();
        
        ClockIn::factory()->create([
            'user_id' => $user->id,
            'registered_at' => '2024-01-15 12:00:00',
        ]);

        $startDate = '2025-01-01 00:00:00';
        $endDate = '2025-01-31 23:59:59';

        $filteredClockIns = ClockIn::inDateRange($startDate, $endDate)->get();

        $this->assertCount(0, $filteredClockIns);
    }

    public function test_clock_in_scope_with_past_dates()
    {
        $user = User::factory()->create();
        
        ClockIn::factory()->create([
            'user_id' => $user->id,
            'registered_at' => '2024-01-15 12:00:00',
        ]);

        $startDate = '2023-01-01 00:00:00';
        $endDate = '2023-01-31 23:59:59';

        $filteredClockIns = ClockIn::inDateRange($startDate, $endDate)->get();

        $this->assertCount(0, $filteredClockIns);
    }
} 