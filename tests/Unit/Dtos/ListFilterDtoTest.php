<?php

namespace Tests\Unit\Dtos;

use App\Dtos\ClockIn\ListFilterDto;
use Carbon\Carbon;
use Tests\TestCase;

class ListFilterDtoTest extends TestCase
{
    public function test_creates_dto_with_both_dates()
    {
        $startDate = Carbon::parse('2024-02-01');
        $endDate = Carbon::parse('2024-02-29');
        
        $dto = new ListFilterDto(
            startDate: $startDate,
            endDate: $endDate
        );

        $this->assertEquals($startDate, $dto->startDate);
        $this->assertEquals($endDate, $dto->endDate);
    }

    public function test_creates_dto_with_null_dates()
    {
        $dto = new ListFilterDto(
            startDate: null,
            endDate: null
        );

        $this->assertNull($dto->startDate);
        $this->assertNull($dto->endDate);
    }

    public function test_create_with_both_dates_in_request()
    {
        $request = [
            'start_date' => '2024-03-01',
            'end_date' => '2024-03-31'
        ];

        $dto = ListFilterDto::create($request);

        $this->assertInstanceOf(Carbon::class, $dto->startDate);
        $this->assertInstanceOf(Carbon::class, $dto->endDate);
        $this->assertEquals('2024-03-01', $dto->startDate->format('Y-m-d'));
        $this->assertEquals('2024-03-31', $dto->endDate->format('Y-m-d'));
    }

    public function test_create_with_only_start_date()
    {
        $request = [
            'start_date' => '2024-04-15'
        ];

        $dto = ListFilterDto::create($request);

        $this->assertInstanceOf(Carbon::class, $dto->startDate);
        $this->assertNull($dto->endDate);
        $this->assertEquals('2024-04-15', $dto->startDate->format('Y-m-d'));
    }

    public function test_create_with_only_end_date()
    {
        $request = [
            'end_date' => '2024-05-20'
        ];

        $dto = ListFilterDto::create($request);

        $this->assertNull($dto->startDate);
        $this->assertInstanceOf(Carbon::class, $dto->endDate);
        $this->assertEquals('2024-05-20', $dto->endDate->format('Y-m-d'));
    }

    public function test_create_with_empty_request()
    {
        $request = [];

        $dto = ListFilterDto::create($request);

        $this->assertNull($dto->startDate);
        $this->assertNull($dto->endDate);
    }

    public function test_create_with_invalid_date_format()
    {
        $request = [
            'start_date' => 'invalid-date',
            'end_date' => '2024-06-30'
        ];

        $this->expectException(\Exception::class);
        ListFilterDto::create($request);
    }

    public function test_properties_are_readonly()
    {
        $dto = new ListFilterDto(
            startDate: Carbon::parse('2024-07-01'),
            endDate: Carbon::parse('2024-07-31')
        );

        $this->assertTrue(property_exists($dto, 'startDate'));
        $this->assertTrue(property_exists($dto, 'endDate'));
        
        $this->assertInstanceOf(Carbon::class, $dto->startDate);
        $this->assertInstanceOf(Carbon::class, $dto->endDate);
    }
} 