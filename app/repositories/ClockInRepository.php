<?php

namespace App\Repositories;

use App\Repositories\Interfaces\ClockInRepositoryInterface;
use App\Dtos\ClockIn\CreateClockInDto;
use App\Models\ClockIn;

class ClockInRepository implements ClockInRepositoryInterface
{
    public function __construct(private ClockIn $model) {}

    public function create(CreateClockInDto $dto): ClockIn
    {
        return $this->model->create($dto->toArray());
    }
}
