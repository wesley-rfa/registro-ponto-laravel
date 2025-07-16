<?php

namespace Database\Seeders;

use App\Enums\UserRoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@teste.com'],
            [
                'name' => 'Admin',
                'cpf' => '000.000.000-00',
                'password' => '12345678',
                'role' => UserRoleEnum::ADMIN,
            ]
        );
    }
}
