<?php

namespace App\Repositories;

use App\Repositories\Interfaces\ClockInRepositoryInterface;
use App\Dtos\ClockIn\CreateClockInDto;
use App\Models\ClockIn;
use App\Exceptions\DuplicateClockInException;

class ClockInRepository implements ClockInRepositoryInterface
{
    public function __construct(private ClockIn $model) {}

    public function create(CreateClockInDto $dto): ClockIn
    {
        $this->validateDuplication($dto);
        
        return $this->model->create($dto->toArray());
    }

    private function validateDuplication(CreateClockInDto $dto): void
    {
        $existingClockIn = $this->model
            ->where('user_id', $dto->user_id)
            ->whereBetween('registered_at', [
                $dto->registered_at->copy()->startOfSecond(),
                $dto->registered_at->copy()->endOfSecond(),
            ])
            ->first();
        
        if ($existingClockIn) {
            throw new DuplicateClockInException(userId: $dto->user_id);
        }
    }
}
