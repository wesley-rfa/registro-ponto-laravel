<?php

namespace Tests\Unit\Dtos;

use App\Dtos\User\CreateUserDto;
use App\Http\Requests\CreateUserRequest;
use App\Models\User;
use App\Enums\UserRoleEnum;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class CreateUserDtoTest extends TestCase
{
    public function test_creates_dto_with_all_properties()
    {
        $dto = new CreateUserDto(
            name: 'Maria Santos Oliveira',
            email: 'maria.santos@empresa.com.br',
            cpf: '12345678901',
            password: 'minhasenha123',
            job_title: 'Analista de Sistemas',
            birth_date: '1985-03-15',
            postal_code: '20040-020',
            address: 'Av. Rio Branco, 156 - Centro',
            role: 'employee',
            created_by: 5
        );

        $this->assertEquals('Maria Santos Oliveira', $dto->name);
        $this->assertEquals('maria.santos@empresa.com.br', $dto->email);
        $this->assertEquals('12345678901', $dto->cpf);
        $this->assertEquals('minhasenha123', $dto->password);
        $this->assertEquals('Analista de Sistemas', $dto->job_title);
        $this->assertEquals('1985-03-15', $dto->birth_date);
        $this->assertEquals('20040-020', $dto->postal_code);
        $this->assertEquals('Av. Rio Branco, 156 - Centro', $dto->address);
        $this->assertEquals('employee', $dto->role);
        $this->assertEquals(5, $dto->created_by);
    }

    public function test_creates_dto_with_minimal_properties()
    {
        $dto = new CreateUserDto(
            name: 'Carlos Eduardo Silva',
            email: 'carlos.silva@tech.com',
            cpf: '98765432100',
            password: 'senha456'
        );

        $this->assertEquals('Carlos Eduardo Silva', $dto->name);
        $this->assertEquals('carlos.silva@tech.com', $dto->email);
        $this->assertEquals('98765432100', $dto->cpf);
        $this->assertEquals('senha456', $dto->password);
        $this->assertNull($dto->job_title);
        $this->assertNull($dto->birth_date);
        $this->assertNull($dto->postal_code);
        $this->assertNull($dto->address);
        $this->assertNull($dto->role);
        $this->assertNull($dto->created_by);
    }

    public function test_create_from_request_with_all_fields()
    {
        $requestData = [
            'name' => 'Ana Paula Costa',
            'email' => 'ana.costa@startup.io',
            'cpf' => '111.222.333-44',
            'password' => 'startup2024',
            'job_title' => 'Desenvolvedora Full Stack',
            'birth_date' => '1992-07-22',
            'postal_code' => '01310-100',
            'address' => 'Av. Paulista, 1000 - Bela Vista',
            'role' => 'admin'
        ];

        $request = new CreateUserRequest($requestData);
        
        $dto = CreateUserDto::createFromRequest($request);

        $this->assertEquals('Ana Paula Costa', $dto->name);
        $this->assertEquals('ana.costa@startup.io', $dto->email);
        $this->assertEquals('11122233344', $dto->cpf); // CPF sem formatação
        $this->assertEquals('startup2024', $dto->password);
        $this->assertEquals('Desenvolvedora Full Stack', $dto->job_title);
        $this->assertEquals('1992-07-22', $dto->birth_date);
        $this->assertEquals('01310-100', $dto->postal_code);
        $this->assertEquals('Av. Paulista, 1000 - Bela Vista', $dto->address);
        $this->assertEquals('admin', $dto->role);
    }

    public function test_create_from_request_with_default_role()
    {
        $requestData = [
            'name' => 'Roberto Almeida',
            'email' => 'roberto.almeida@consultoria.com',
            'cpf' => '555.666.777-88',
            'password' => 'consultoria123',
            'job_title' => 'Consultor de Negócios',
            'birth_date' => '1978-11-08',
            'postal_code' => '04567-890',
            'address' => 'Rua das Palmeiras, 45 - Jardins'
        ];

        $request = new CreateUserRequest($requestData);
        
        $dto = CreateUserDto::createFromRequest($request);

        $this->assertEquals(UserRoleEnum::EMPLOYEE->value, $dto->role);
    }

    public function test_create_from_request_with_authenticated_user()
    {
        $expectedUserId = 42;
        $requestData = [
            'name' => 'Fernanda Lima',
            'email' => 'fernanda.lima@inovacao.com',
            'cpf' => '999.888.777-66',
            'password' => 'inovacao2024'
        ];
        $request = new class($requestData, $expectedUserId) extends \App\Http\Requests\CreateUserRequest {
            private $fakeUserId;
            public function __construct($data, $fakeUserId) {
                parent::__construct($data);
                $this->fakeUserId = $fakeUserId;
            }
            public function user($guard = null) { return (object)['id' => $this->fakeUserId]; }
        };
        $dto = CreateUserDto::createFromRequest($request);
        $this->assertEquals($expectedUserId, $dto->created_by);
    }

    public function test_to_array_returns_all_properties()
    {
        $dto = new CreateUserDto(
            name: 'Lucas Mendes',
            email: 'lucas.mendes@fintech.com',
            cpf: '33344455566',
            password: 'fintech2024',
            job_title: 'Product Manager',
            birth_date: '1989-04-12',
            postal_code: '01234-567',
            address: 'Rua Augusta, 789 - Consolação',
            role: 'employee',
            created_by: 15
        );

        $array = $dto->toArray();

        $expected = [
            'name' => 'Lucas Mendes',
            'email' => 'lucas.mendes@fintech.com',
            'cpf' => '33344455566',
            'password' => 'fintech2024',
            'job_title' => 'Product Manager',
            'birth_date' => '1989-04-12',
            'postal_code' => '01234-567',
            'address' => 'Rua Augusta, 789 - Consolação',
            'role' => 'employee',
            'created_by' => 15,
        ];

        $this->assertEquals($expected, $array);
    }

    public function test_to_model_creates_user_instance()
    {
        $dto = new CreateUserDto(
            name: 'Patrícia Santos',
            email: 'patricia.santos@saude.com',
            cpf: '77788899900',
            password: 'saude2024',
            job_title: 'Enfermeira',
            birth_date: '1991-09-30',
            postal_code: '05422-030',
            address: 'Rua Cardeal Arcoverde, 123 - Pinheiros',
            role: 'employee',
            created_by: 8
        );

        $user = $dto->toModel();

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Patrícia Santos', $user->name);
        $this->assertEquals('patricia.santos@saude.com', $user->email);
        $this->assertEquals('77788899900', $user->cpf);
        // Não testamos a senha pois ela é hasheada pelo model
        $this->assertEquals('Enfermeira', $user->job_title);
        // birth_date pode ser Carbon ou string, então comparar como string
        $this->assertEquals('1991-09-30',
            method_exists($user->birth_date, 'toDateString')
                ? $user->birth_date->toDateString()
                : $user->birth_date
        );
        $this->assertEquals('05422-030', $user->postal_code);
        $this->assertEquals('Rua Cardeal Arcoverde, 123 - Pinheiros', $user->address);
        $roleValue = $user->role instanceof \BackedEnum ? $user->role->value : (string)$user->role;
        $this->assertEquals('employee', $roleValue);
        $this->assertEquals(8, $user->created_by);
    }
} 