<?php

namespace App\Repositories;

use App\Enums\UserRoleEnum;
use App\Dtos\User\CreateUserDto;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(private User $model) {
        $this->model = $model;
    }

    public function findAll(): LengthAwarePaginator
    {
        return $this->model->with('creator')
            ->where('role', UserRoleEnum::EMPLOYEE->value)
            ->orderBy('name')
            ->paginate(15);
    }

    public function create(CreateUserDto $dto): User
    {
        return $this->model->create($dto->toArray());
    }
}