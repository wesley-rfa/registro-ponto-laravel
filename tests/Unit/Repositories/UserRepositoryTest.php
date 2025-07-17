<?php

namespace Tests\Unit\Repositories;

use App\Repositories\UserRepository;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Tests\TestCase;

class UserRepositoryTest extends TestCase
{
    private $userModelMock;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userModelMock = Mockery::mock(User::class);
        $this->userRepository = new UserRepository($this->userModelMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_find_all_returns_paginator()
    {
        $expectedPaginator = new LengthAwarePaginator([], 0, 15);

        $this->userModelMock
            ->shouldReceive('with')
            ->once()
            ->with('creator')
            ->andReturnSelf();
        $this->userModelMock
            ->shouldReceive('where')
            ->once()
            ->with('role', Mockery::any())
            ->andReturnSelf();
        $this->userModelMock
            ->shouldReceive('orderBy')
            ->once()
            ->with('name')
            ->andReturnSelf();
        $this->userModelMock
            ->shouldReceive('paginate')
            ->once()
            ->with(15)
            ->andReturn($expectedPaginator);

        $result = $this->userRepository->findAll();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertSame($expectedPaginator, $result);
    }

    public function test_find_by_id_returns_user_when_found()
    {
        $dto = new \App\Dtos\User\FindUserDto(1);
        $expectedUser = new User(['name' => 'João Silva', 'email' => 'joao@exemplo.com']);
        $expectedUser->id = 1;

        $this->userModelMock
            ->shouldReceive('findOrFail')
            ->once()
            ->with(1)
            ->andReturn($expectedUser);

        $result = $this->userRepository->findById($dto);

        $this->assertInstanceOf(User::class, $result);
        $this->assertSame($expectedUser, $result);
        $this->assertEquals(1, $result->id);
    }

    public function test_create_returns_new_user()
    {
        $dto = new \App\Dtos\User\CreateUserDto(
            name: 'João Silva',
            email: 'joao@exemplo.com',
            cpf: '52998224725',
            password: 'senha123456',
            job_title: 'Desenvolvedor',
            birth_date: '1990-01-01',
            postal_code: '01001-000',
            address: 'Rua das Flores, 123',
            role: 'employee',
            created_by: null,
        );
        $expectedUser = new User($dto->toArray());
        $expectedUser->id = 1;

        $this->userModelMock
            ->shouldReceive('create')
            ->once()
            ->with($dto->toArray())
            ->andReturn($expectedUser);

        $result = $this->userRepository->create($dto);

        $this->assertInstanceOf(User::class, $result);
        $this->assertSame($expectedUser, $result);
        $this->assertEquals('João Silva', $result->name);
        $this->assertEquals('joao@exemplo.com', $result->email);
    }

    public function test_delete_returns_true_when_successful()
    {
        $dto = new \App\Dtos\User\DeleteUserDto(1);
        $user = Mockery::mock(User::class);
        $user->shouldReceive('delete')->once()->andReturn(true);

        $this->userModelMock
            ->shouldReceive('findOrFail')
            ->once()
            ->with(1)
            ->andReturn($user);

        $result = $this->userRepository->delete($dto);
        $this->assertTrue($result);
    }

    public function test_delete_returns_false_when_failed()
    {
        $dto = new \App\Dtos\User\DeleteUserDto(1);
        $user = Mockery::mock(User::class);
        $user->shouldReceive('delete')->once()->andReturn(false);

        $this->userModelMock
            ->shouldReceive('findOrFail')
            ->once()
            ->with(1)
            ->andReturn($user);

        $result = $this->userRepository->delete($dto);
        $this->assertFalse($result);
    }

    public function test_exists_by_cpf_returns_true_when_exists()
    {
        $cpf = '52998224725';
        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('exists')->once()->andReturn(true);

        $this->userModelMock
            ->shouldReceive('where')
            ->once()
            ->with('cpf', $cpf)
            ->andReturn($queryMock);

        $result = $this->userRepository->existsByCpf($cpf);
        $this->assertTrue($result);
    }

    public function test_exists_by_cpf_returns_false_when_not_exists()
    {
        $cpf = '52998224725';
        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('exists')->once()->andReturn(false);

        $this->userModelMock
            ->shouldReceive('where')
            ->once()
            ->with('cpf', $cpf)
            ->andReturn($queryMock);

        $result = $this->userRepository->existsByCpf($cpf);
        $this->assertFalse($result);
    }

    public function test_exists_by_cpf_ignores_user_id()
    {
        $cpf = '52998224725';
        $ignoreUserId = 2;
        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('where')->once()->with('id', '!=', $ignoreUserId)->andReturnSelf();
        $queryMock->shouldReceive('exists')->once()->andReturn(true);

        $this->userModelMock
            ->shouldReceive('where')
            ->once()
            ->with('cpf', $cpf)
            ->andReturn($queryMock);

        $result = $this->userRepository->existsByCpf($cpf, $ignoreUserId);
        $this->assertTrue($result);
    }
} 