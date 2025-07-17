<?php

namespace Tests\Feature\Repositories;

use App\Repositories\UserRepository;
use App\Models\User;
use App\Dtos\User\CreateUserDto;
use App\Dtos\User\UpdateUserDto;
use App\Dtos\User\DeleteUserDto;
use App\Dtos\User\FindUserDto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Enums\UserRoleEnum;

class UserRepositoryIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = new UserRepository(new User());
    }

    public function test_find_all_returns_paginator_with_various_data()
    {
        User::factory()->count(3)->create(['role' => UserRoleEnum::EMPLOYEE->value]);
        User::factory()->create(['role' => UserRoleEnum::ADMIN->value]);
        User::factory()->create(['role' => UserRoleEnum::EMPLOYEE->value]);

        $result = $this->userRepository->findAll();

        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $result);
        $this->assertEquals(4, $result->total());
        $this->assertEquals(15, $result->perPage());
    }

    public function test_find_by_id_returns_user_when_found()
    {
        $user = User::factory()->create(['name' => 'Maria Silva', 'email' => 'maria@exemplo.com']);
        $dto = new FindUserDto($user->id);

        $result = $this->userRepository->findById($dto);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($user->id, $result->id);
        $this->assertEquals('Maria Silva', $result->name);
        $this->assertEquals('maria@exemplo.com', $result->email);
    }

    public function test_create_returns_new_user_with_complete_data()
    {
        $dto = new CreateUserDto(
            name: 'Pedro Santos',
            email: 'pedro@exemplo.com',
            cpf: '12345678901',
            password: 'senha123456',
            job_title: 'Analista de Sistemas',
            birth_date: '1985-05-15',
            postal_code: '20000-000',
            address: 'Av. Rio Branco, 100',
            role: 'employee',
            created_by: null,
        );

        $result = $this->userRepository->create($dto);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals('Pedro Santos', $result->name);
        $this->assertEquals('pedro@exemplo.com', $result->email);
        $this->assertEquals('12345678901', $result->cpf);
        $this->assertEquals('Analista de Sistemas', $result->job_title);
        $this->assertDatabaseHas('users', [
            'name' => 'Pedro Santos',
            'email' => 'pedro@exemplo.com',
            'cpf' => '12345678901',
        ]);
    }

    public function test_create_returns_new_manager_user()
    {
        $dto = new CreateUserDto(
            name: 'Ana Costa',
            email: 'ana@exemplo.com',
            cpf: '98765432100',
            password: 'senha123456',
            job_title: 'Gerente de Projetos',
            birth_date: '1980-10-20',
            postal_code: '30000-000',
            address: 'Rua das Palmeiras, 50',
            role: UserRoleEnum::ADMIN->value,
            created_by: null,
        );

        $result = $this->userRepository->create($dto);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals('Ana Costa', $result->name);
        $this->assertEquals(UserRoleEnum::ADMIN, $result->role);
        $this->assertDatabaseHas('users', [
            'name' => 'Ana Costa',
            'role' => UserRoleEnum::ADMIN->value,
        ]);
    }

    public function test_update_returns_updated_user_with_new_data()
    {
        $user = User::factory()->create(['name' => 'João Original', 'email' => 'joao@exemplo.com']);
        
        $dto = new UpdateUserDto(
            name: 'João Atualizado',
            email: 'joao.novo@exemplo.com',
            cpf: '11122233344',
            job_title: 'Desenvolvedor Senior',
            birth_date: '1992-03-10',
            postal_code: '40000-000',
            address: 'Rua Nova, 200',
            role: 'employee',
        );

        $result = $this->userRepository->update($user, $dto);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals('João Atualizado', $result->name);
        $this->assertEquals('joao.novo@exemplo.com', $result->email);
        $this->assertEquals('11122233344', $result->cpf);
        $this->assertEquals('Desenvolvedor Senior', $result->job_title);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'João Atualizado',
            'email' => 'joao.novo@exemplo.com',
        ]);
    }

    public function test_delete_returns_true_when_successful()
    {
        $user = User::factory()->create(['name' => 'Usuário para Deletar']);
        $dto = new DeleteUserDto($user->id);

        $result = $this->userRepository->delete($dto);

        $this->assertTrue($result);
        $this->assertSoftDeleted($user);
    }

    public function test_delete_throws_exception_when_user_not_found()
    {
        $dto = new DeleteUserDto(999);
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->userRepository->delete($dto);
    }

    public function test_exists_by_cpf_returns_true_when_user_exists()
    {
        $cpf = '55566677788';
        $user = User::factory()->create(['cpf' => $cpf]);

        $result = $this->userRepository->existsByCpf($cpf);

        $this->assertTrue($result);
    }

    public function test_exists_by_cpf_returns_false_when_user_not_exists()
    {
        $result = $this->userRepository->existsByCpf('99988877766');

        $this->assertFalse($result);
    }

    public function test_exists_by_cpf_with_different_cpfs()
    {
        $cpf1 = '11122233344';
        $cpf2 = '55566677788';
        
        User::factory()->create(['cpf' => $cpf1]);

        $this->assertTrue($this->userRepository->existsByCpf($cpf1));
        $this->assertFalse($this->userRepository->existsByCpf($cpf2));
    }
} 