<?php

namespace App\Services;

use App\Dtos\ClockIn\CreateClockInDto;
use App\Dtos\ClockIn\ListFilterDto;
use App\Repositories\Interfaces\ClockInRepositoryInterface;
use App\Models\ClockIn;
use Illuminate\Pagination\LengthAwarePaginator;

class ClockInService
{
    public function __construct(private ClockInRepositoryInterface $clockInRepository) {}

    public function findAll(ListFilterDto $dto): LengthAwarePaginator
    {
        return $this->clockInRepository->findAll($dto);
    }

    public function create(CreateClockInDto $dto): ClockIn
    {
        return $this->clockInRepository->create($dto);
    }
}
