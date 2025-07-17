<?php

namespace App\Dtos\User;

class DeleteUserDto
{
    public function __construct(
        public readonly int $userId,
    ) {}

    public static function createFromId(int $userId): self
    {
        return new self($userId);
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
        ];
    }
} 