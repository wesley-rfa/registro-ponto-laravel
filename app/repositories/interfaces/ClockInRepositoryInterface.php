<?php

namespace App\Repositories\Interfaces;

use App\Dtos\ClockIn\CreateClockInDto;
use App\Models\ClockIn;

interface ClockInRepositoryInterface
{
    public function create(CreateClockInDto $dto): ClockIn;
}