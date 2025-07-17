<?php

namespace Tests\Unit\Services;

use App\Services\UserService;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Dtos\User\CreateUserDto;
use App\Dtos\User\UpdateUserDto;
use App\Dtos\User\DeleteUserDto;
use App\Dtos\User\FindUserDto;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Tests\TestCase;

/**
 * @mixin \Mockery\MockInterface
 */
class UserServiceTest extends TestCase
{
    /** @var \Mockery\MockInterface&\App\Repositories\Interfaces\UserRepositoryInterface */
    private $mockRepository;
    private UserService $userService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->userService = new UserService($this->mockRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_find_all_returns_paginator()
    {
        $expectedPaginator = new LengthAwarePaginator([], 0, 15);
        
        $this->mockRepository
            ->shouldReceive('findAll')
            ->once()
            ->andReturn($expectedPaginator);

        $result = $this->userService->findAll();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertSame($expectedPaginator, $result);
    }

    public function test_find_by_id_returns_user_when_found()
    {
        $dto = new FindUserDto(1);
        $expectedUser = new User(['name' => 'João Silva', 'email' => 'joao@exemplo.com']);
        $expectedUser->id = 1;

        $this->mockRepository
            ->shouldReceive('findById')
            ->once()
            ->with($dto)
            ->andReturn($expectedUser);

        $result = $this->userService->findById($dto);

        $this->assertInstanceOf(User::class, $result);
        $this->assertSame($expectedUser, $result);
        $this->assertEquals(1, $result->id);
    }

    public function test_find_by_id_returns_null_when_not_found()
    {
        $dto = new FindUserDto(999);

        $this->mockRepository
            ->shouldReceive('findById')
            ->once()
            ->with($dto)
            ->andReturn(null);

        $result = $this->userService->findById($dto);

        $this->assertNull($result);
    }

    public function test_create_returns_new_user()
    {
        $dto = new CreateUserDto(
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

        $this->mockRepository
            ->shouldReceive('create')
            ->once()
            ->with($dto)
            ->andReturn($expectedUser);

        $result = $this->userService->create($dto);

        $this->assertInstanceOf(User::class, $result);
        $this->assertSame($expectedUser, $result);
        $this->assertEquals('João Silva', $result->name);
        $this->assertEquals('joao@exemplo.com', $result->email);
    }

    public function test_update_returns_updated_user()
    {
        $user = new User(['name' => 'João Silva', 'email' => 'joao@exemplo.com']);
        $user->id = 1;

        $dto = new UpdateUserDto(
            name: 'João Silva Santos',
            email: 'joao.santos@exemplo.com',
            cpf: '52998224725',
            job_title: 'Desenvolvedor Senior',
            birth_date: '1990-01-01',
            postal_code: '01001-000',
            address: 'Rua das Flores, 456',
            role: 'employee',
        );

        $updatedUser = new User($dto->toArray());
        $updatedUser->id = 1;

        $this->mockRepository
            ->shouldReceive('update')
            ->once()
            ->with($user, $dto)
            ->andReturn($updatedUser);

        $result = $this->userService->update($user, $dto);

        $this->assertInstanceOf(User::class, $result);
        $this->assertSame($updatedUser, $result);
        $this->assertEquals('João Silva Santos', $result->name);
        $this->assertEquals('joao.santos@exemplo.com', $result->email);
    }

    public function test_delete_returns_true_when_successful()
    {
        $dto = new DeleteUserDto(1);

        $this->mockRepository
            ->shouldReceive('delete')
            ->once()
            ->with($dto)
            ->andReturn(true);

        $result = $this->userService->delete($dto);

        $this->assertTrue($result);
    }

    public function test_delete_returns_false_when_failed()
    {
        $dto = new DeleteUserDto(999);

        $this->mockRepository
            ->shouldReceive('delete')
            ->once()
            ->with($dto)
            ->andReturn(false);

        $result = $this->userService->delete($dto);

        $this->assertFalse($result);
    }

    public function test_service_handles_repository_exceptions()
    {
        $dto = new FindUserDto(1);

        $this->mockRepository
            ->shouldReceive('findById')
            ->once()
            ->with($dto)
            ->andThrow(new \Exception('Erro no repositório'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Erro no repositório');

        $this->userService->findById($dto);
    }
} 