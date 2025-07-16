<?php

namespace App\Services;

use App\Dtos\ClockIn\CreateClockInDto;
use App\Repositories\Interfaces\ClockInRepositoryInterface;
use App\Models\ClockIn;

class ClockInService
{
    public function __construct(private ClockInRepositoryInterface $clockInRepository) {}

    public function create(CreateClockInDto $dto): ClockIn
    {
        return $this->clockInRepository->create($dto);
    }
}
