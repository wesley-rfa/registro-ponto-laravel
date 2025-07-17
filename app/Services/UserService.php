<?php

namespace App\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Dtos\User\CreateUserDto;
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

    public function create(CreateUserDto $dto): User
    {
        return $this->userRepository->create($dto);
    }
}   