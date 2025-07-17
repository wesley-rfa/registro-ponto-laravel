<?php

namespace App\Repositories;

use App\Enums\UserRoleEnum;
use App\Dtos\User\CreateUserDto;
use App\Dtos\User\UpdateUserDto;
use App\Dtos\User\DeleteUserDto;
use App\Dtos\User\FindUserDto;
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

    public function findById(FindUserDto $dto): ?User
    {
        return $this->model->findOrFail($dto->userId);
    }

    public function create(CreateUserDto $dto): User
    {
        return $this->model->create($dto->toArray());
    }

    public function update($user, UpdateUserDto $dto): User
    {
        $user->update($dto->toArray());
        return $user->fresh();
    }

    public function delete(DeleteUserDto $dto): bool
    {
        $user = $this->model->findOrFail($dto->userId);

        return $user->delete();
    }

    public function existsByCpf(string $cpf, ?int $ignoreUserId = null): bool
    {
        $query = $this->model->where('cpf', $cpf);
        if ($ignoreUserId) {
            $query->where('id', '!=', $ignoreUserId);
        }
        return $query->exists();
    }
}