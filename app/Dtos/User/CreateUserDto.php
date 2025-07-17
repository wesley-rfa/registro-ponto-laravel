<?php

namespace App\Dtos\User;

use App\Http\Requests\CreateUserRequest;
use App\Models\User;
use App\Enums\UserRoleEnum;
use App\Helpers\CpfHelper;

class CreateUserDto
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $cpf,
        public readonly string $password,
        public readonly ?string $job_title = null,
        public readonly ?string $birth_date = null,
        public readonly ?string $postal_code = null,
        public readonly ?string $address = null,
        public readonly ?string $role = null,
        public readonly ?int $created_by = null,
    ) {}

    public static function createFromRequest(CreateUserRequest $request): self
    {
        return new self(
            name: $request->name,
            email: $request->email,
            cpf: CpfHelper::unformat($request->cpf),
            password: $request->password,
            job_title: $request->job_title,
            birth_date: $request->birth_date,
            postal_code: $request->postal_code,
            address: $request->address,
            role: $request->role ?? UserRoleEnum::EMPLOYEE->value,
            created_by: $request->user()?->id,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'cpf' => $this->cpf,
            'password' => $this->password,
            'job_title' => $this->job_title,
            'birth_date' => $this->birth_date,
            'postal_code' => $this->postal_code,
            'address' => $this->address,
            'role' => $this->role,
            'created_by' => $this->created_by,
        ];
    }

    public function toModel(): User
    {
        return new User($this->toArray());
    }
}