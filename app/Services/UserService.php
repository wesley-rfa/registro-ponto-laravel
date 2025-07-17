<?php

namespace App\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Dtos\User\CreateUserDto;
use App\Dtos\User\UpdateUserDto;
use App\Dtos\User\DeleteUserDto;
use App\Dtos\User\FindUserDto;
use App\Models\User;

class UserService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function findAll(): LengthAwarePaginator
    {
        return $this->userRepository->findAll();
    }

    public function findById(FindUserDto $dto): ?User
    {
        return $this->userRepository->findById($dto);
    }

    public function create(CreateUserDto $dto): User
    {
        return $this->userRepository->create($dto);
    }

    public function update(User $user, UpdateUserDto $dto): User
    {
        return $this->userRepository->update($user, $dto);
    }

    public function delete(DeleteUserDto $dto): bool
    {
        return $this->userRepository->delete($dto);
    }
}   