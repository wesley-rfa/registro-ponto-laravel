<?php

namespace App\Repositories\Interfaces;

use App\Dtos\User\CreateUserDto;
use App\Dtos\User\UpdateUserDto;
use App\Dtos\User\DeleteUserDto;
use App\Dtos\User\FindUserDto;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    public function findAll(): LengthAwarePaginator;
    public function findById(FindUserDto $dto): ?User;
    public function create(CreateUserDto $dto): User;
    public function update(User $user, UpdateUserDto $dto): User;
    public function delete(DeleteUserDto $dto): bool;
    public function existsByCpf(string $cpf, ?int $ignoreUserId = null): bool;
}