<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\UserService;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class UserCreationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_admin_can_create_user()
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        
        $userData = [
            'name' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'password' => 'senha123456',
            'password_confirmation' => 'senha123456',
            'cpf' => '529.982.247-25',
            'job_title' => 'Desenvolvedor',
            'birth_date' => '1990-01-01',
            'postal_code' => '01001-000',
            'address' => 'Rua das Flores, 123'
        ];

        $response = $this->actingAs($admin)
            ->post(route('admin.users.store'), $userData);

        $response->assertRedirect(route('admin.users'));
        $response->assertSessionHas('success', 'Funcionário cadastrado com sucesso!');

        $this->assertDatabaseHas('users', [
            'name' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'cpf' => '52998224725',
            'job_title' => 'Desenvolvedor',
            'birth_date' => '1990-01-01 00:00:00',
            'postal_code' => '01001-000',
            'address' => 'Rua das Flores, 123',
            'role' => 'employee'
        ]);
    }

    public function test_user_creation_validates_required_fields()
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        
        $response = $this->actingAs($admin)
            ->post(route('admin.users.store'), []);

        $response->assertSessionHasErrors(['name', 'email', 'password', 'cpf', 'job_title', 'birth_date', 'postal_code', 'address']);
    }

    public function test_user_creation_validates_password_confirmation()
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        
        $userData = [
            'name' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'password' => 'senha123456',
            'password_confirmation' => 'senha1234567',
            'cpf' => '529.982.247-25',
            'job_title' => 'Desenvolvedor',
            'birth_date' => '1990-01-01',
            'postal_code' => '01001-000',
            'address' => 'Rua das Flores, 123'
        ];

        $response = $this->actingAs($admin)
            ->post(route('admin.users.store'), $userData);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_user_creation_validates_password_minimum_length()
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        
        $userData = [
            'name' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'password' => '1234567',
            'password_confirmation' => '1234567',
            'cpf' => '529.982.247-25',
            'job_title' => 'Desenvolvedor',
            'birth_date' => '1990-01-01',
            'postal_code' => '01001-000',
            'address' => 'Rua das Flores, 123'
        ];

        $response = $this->actingAs($admin)
            ->post(route('admin.users.store'), $userData);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_user_creation_validates_cpf_format()
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        
        $userData = [
            'name' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'password' => 'senha123456',
            'password_confirmation' => 'senha123456',
            'cpf' => '123.456.789-10',
            'job_title' => 'Desenvolvedor',
            'birth_date' => '1990-01-01',
            'postal_code' => '01001-000',
            'address' => 'Rua das Flores, 123'
        ];

        $response = $this->actingAs($admin)
            ->post(route('admin.users.store'), $userData);

        $response->assertSessionHasErrors(['cpf']);
    }

    public function test_user_creation_validates_cep_format()
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        
        $userData = [
            'name' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'password' => 'senha123456',
            'password_confirmation' => 'senha123456',
            'cpf' => '529.982.247-25',
            'job_title' => 'Desenvolvedor',
            'birth_date' => '1990-01-01',
            'postal_code' => '01001-0000',
            'address' => 'Rua das Flores, 123'
        ];

        $response = $this->actingAs($admin)
            ->post(route('admin.users.store'), $userData);

        $response->assertSessionHasErrors(['postal_code']);
    }

    public function test_user_creation_validates_unique_email()
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        /** @var User $existingUser */
        $existingUser = User::factory()->create(['email' => 'joao@exemplo.com']);
        
        $userData = [
            'name' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'password' => 'senha123456',
            'password_confirmation' => 'senha123456',
            'cpf' => '529.982.247-25',
            'job_title' => 'Desenvolvedor',
            'birth_date' => '1990-01-01',
            'postal_code' => '01001-000',
            'address' => 'Rua das Flores, 123'
        ];

        $response = $this->actingAs($admin)
            ->post(route('admin.users.store'), $userData);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_user_creation_validates_unique_cpf()
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        /** @var User $existingUser */
        $existingUser = User::factory()->create(['cpf' => '52998224725']);
        
        $userData = [
            'name' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'password' => 'senha123456',
            'password_confirmation' => 'senha123456',
            'cpf' => '529.982.247-25',
            'job_title' => 'Desenvolvedor',
            'birth_date' => '1990-01-01',
            'postal_code' => '01001-000',
            'address' => 'Rua das Flores, 123'
        ];

        $response = $this->actingAs($admin)
            ->post(route('admin.users.store'), $userData);

        $response->assertSessionHasErrors(['cpf']);
    }

    public function test_user_creation_with_valid_cep()
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        
        $userData = [
            'name' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'password' => 'senha123456',
            'password_confirmation' => 'senha123456',
            'cpf' => '529.982.247-25',
            'job_title' => 'Desenvolvedor',
            'birth_date' => '1990-01-01',
            'postal_code' => '01001-000',
            'address' => 'Rua das Flores, 123'
        ];

        $response = $this->actingAs($admin)
            ->post(route('admin.users.store'), $userData);

        $response->assertRedirect(route('admin.users'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'cpf' => '52998224725',
            'job_title' => 'Desenvolvedor',
            'birth_date' => '1990-01-01 00:00:00',
            'postal_code' => '01001-000',
            'address' => 'Rua das Flores, 123',
            'role' => 'employee'
        ]);
    }

    public function test_user_service_creates_user_with_sanitized_cpf()
    {
        $mockRepository = Mockery::mock(UserRepository::class);
        
        $userData = [
            'name' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'password' => 'senha123456',
            'password_confirmation' => 'senha123456',
            'cpf' => '529.982.247-25',
            'job_title' => 'Desenvolvedor',
            'birth_date' => '1990-01-01',
            'postal_code' => '01001-000',
            'address' => 'Rua das Flores, 123'
        ];

        $expectedData = [
            'name' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'cpf' => '52998224725',
            'password' => 'senha123456',
            'job_title' => 'Desenvolvedor',
            'birth_date' => '1990-01-01',
            'postal_code' => '01001-000',
            'address' => 'Rua das Flores, 123',
            'role' => 'employee',
            'created_by' => null,
        ];

        $mockUser = new User($expectedData);
        $mockUser->id = 1;

        $mockRepository->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($dto) use ($expectedData) {
                return $dto->toArray() === $expectedData;
            }))
            ->andReturn($mockUser);

        $userService = new UserService($mockRepository);
        
        $dto = \App\Dtos\User\CreateUserDto::createFromRequest(
            new \App\Http\Requests\CreateUserRequest($userData)
        );
        
        $result = $userService->create($dto);
        
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals('52998224725', $result->cpf);
    }
} 