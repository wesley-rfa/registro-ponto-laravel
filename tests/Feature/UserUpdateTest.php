<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\UserService;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;

class UserUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_admin_can_update_user()
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        
        /** @var User $user */
        $user = User::factory()->create([
            'name' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'cpf' => '52998224725',
            'job_title' => 'Desenvolvedor',
            'birth_date' => '1990-01-01',
            'postal_code' => '01001-000',
            'address' => 'Rua das Flores, 123'
        ]);

        $updateData = [
            'name' => 'João Silva Santos',
            'email' => 'joao.santos@exemplo.com',
            'cpf' => '529.982.247-25',
            'job_title' => 'Desenvolvedor Senior',
            'birth_date' => '1990-01-01',
            'postal_code' => '01001-000',
            'address' => 'Rua das Flores, 456'
        ];

        $response = $this->actingAs($admin)
            ->put(route('admin.users.update', $user), $updateData);

        $response->assertRedirect(route('admin.users'));
        $response->assertSessionHas('success', 'Usuário atualizado com sucesso!');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'João Silva Santos',
            'email' => 'joao.santos@exemplo.com',
            'cpf' => '52998224725',
            'job_title' => 'Desenvolvedor Senior',
            'address' => 'Rua das Flores, 456',
        ]);
    }

    public function test_admin_can_update_user_with_password()
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        
        /** @var User $user */
        $user = User::factory()->create([
            'name' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'cpf' => '52998224725',
        ]);

        $updateData = [
            'name' => 'João Silva Santos',
            'email' => 'joao.santos@exemplo.com',
            'cpf' => '529.982.247-25',
            'password' => 'novaSenha123',
            'password_confirmation' => 'novaSenha123',
            'job_title' => 'Desenvolvedor Senior',
            'birth_date' => '1990-01-01',
            'postal_code' => '01001-000',
            'address' => 'Rua das Flores, 456'
        ];

        $response = $this->actingAs($admin)
            ->put(route('admin.users.update', $user), $updateData);

        $response->assertRedirect(route('admin.users'));
        $response->assertSessionHas('success', 'Usuário atualizado com sucesso!');

        // Verifica se a senha foi atualizada
        $user->refresh();
        $this->assertTrue(Hash::check('novaSenha123', $user->password));
    }

    public function test_admin_cannot_update_user_with_invalid_email()
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        
        /** @var User $user1 */
        $user1 = User::factory()->create(['email' => 'user1@exemplo.com']);
        
        /** @var User $user2 */
        $user2 = User::factory()->create(['email' => 'user2@exemplo.com']);

        $updateData = [
            'name' => 'João Silva Santos',
            'email' => 'user1@exemplo.com', // Email já existe
            'cpf' => '529.982.247-25',
            'job_title' => 'Desenvolvedor Senior',
            'birth_date' => '1990-01-01',
            'postal_code' => '01001-000',
            'address' => 'Rua das Flores, 456'
        ];

        $response = $this->actingAs($admin)
            ->put(route('admin.users.update', $user2), $updateData);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_admin_can_update_user_with_same_email()
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        
        /** @var User $user */
        $user = User::factory()->create([
            'name' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'cpf' => '52998224725',
        ]);

        $updateData = [
            'name' => 'João Silva Santos',
            'email' => 'joao@exemplo.com', // Mesmo email
            'cpf' => '529.982.247-25',
            'job_title' => 'Desenvolvedor Senior',
            'birth_date' => '1990-01-01',
            'postal_code' => '01001-000',
            'address' => 'Rua das Flores, 456'
        ];

        $response = $this->actingAs($admin)
            ->put(route('admin.users.update', $user), $updateData);

        $response->assertRedirect(route('admin.users'));
        $response->assertSessionHas('success', 'Usuário atualizado com sucesso!');
    }

    public function test_user_service_updates_user_with_sanitized_cpf()
    {
        $mockRepository = Mockery::mock(UserRepository::class);
        
        $user = new User([
            'name' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'cpf' => '52998224725',
        ]);
        $user->id = 1;
        
        $updateData = [
            'name' => 'João Silva Santos',
            'email' => 'joao.santos@exemplo.com',
            'cpf' => '529.982.247-25',
            'job_title' => 'Desenvolvedor Senior',
            'birth_date' => '1990-01-01',
            'postal_code' => '01001-000',
            'address' => 'Rua das Flores, 456'
        ];

        $expectedData = [
            'name' => 'João Silva Santos',
            'email' => 'joao.santos@exemplo.com',
            'cpf' => '52998224725',
            'job_title' => 'Desenvolvedor Senior',
            'birth_date' => '1990-01-01',
            'postal_code' => '01001-000',
            'address' => 'Rua das Flores, 456',
            'role' => 'employee',
        ];

        $updatedUser = new User($expectedData);
        $updatedUser->id = 1;

        $mockRepository->shouldReceive('update')
            ->once()
            ->with($user, Mockery::on(function ($dto) use ($expectedData) {
                return $dto->toArray() === $expectedData;
            }))
            ->andReturn($updatedUser);

        $userService = new UserService($mockRepository);
        
        $dto = \App\Dtos\User\UpdateUserDto::createFromRequest(
            new \App\Http\Requests\UpdateUserRequest($updateData)
        );
        
        $result = $userService->update($user, $dto);
        
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals('52998224725', $result->cpf);
    }
} 