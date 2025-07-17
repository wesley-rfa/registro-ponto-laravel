<?php

namespace App\Dtos\User;

use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Enums\UserRoleEnum;
use App\Helpers\CpfHelper;

class UpdateUserDto
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $cpf,
        public readonly ?string $password = null,
        public readonly ?string $job_title = null,
        public readonly ?string $birth_date = null,
        public readonly ?string $postal_code = null,
        public readonly ?string $address = null,
        public readonly ?string $role = null,
        public readonly ?int $updated_by = null,
    ) {}

    public static function createFromRequest(UpdateUserRequest $request): self
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
            updated_by: $request->user()?->id,
        );
    }

    public function toArray(): array
    {
        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'cpf' => $this->cpf,
            'job_title' => $this->job_title,
            'birth_date' => $this->birth_date,
            'postal_code' => $this->postal_code,
            'address' => $this->address,
            'role' => $this->role,
        ];
        
        if ($this->password) {
            $data['password'] = $this->password;
        }

        return $data;
    }
} 