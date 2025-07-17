<?php

namespace App\Repositories\Interfaces;

use App\Dtos\User\CreateUserDto;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    public function findAll(): LengthAwarePaginator;
    public function create(CreateUserDto $dto): User;
}