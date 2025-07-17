<?php

namespace App\Repositories\Interfaces;

use App\Dtos\ClockIn\CreateClockInDto;
use App\Dtos\ClockIn\ListFilterDto;
use App\Models\ClockIn;
use Illuminate\Pagination\LengthAwarePaginator;

interface ClockInRepositoryInterface
{
    public function findAll(ListFilterDto $dto): LengthAwarePaginator;
    public function create(CreateClockInDto $dto): ClockIn;
}