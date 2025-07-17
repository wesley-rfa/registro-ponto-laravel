<?php

namespace Tests\Unit\Dtos;

use App\Dtos\User\UpdateUserDto;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Enums\UserRoleEnum;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class UpdateUserDtoTest extends TestCase
{
    public function test_creates_dto_with_all_properties()
    {
        $dto = new UpdateUserDto(
            name: 'Rafael Costa Mendes',
            email: 'rafael.mendes@tecnologia.com',
            cpf: '45678912300',
            password: 'tecnologia2024',
            job_title: 'Arquiteto de Software',
            birth_date: '1987-12-05',
            postal_code: '01452-002',
            address: 'Alameda Santos, 2000 - Jardins',
            role: 'admin',
            updated_by: 12
        );

        $this->assertEquals('Rafael Costa Mendes', $dto->name);
        $this->assertEquals('rafael.mendes@tecnologia.com', $dto->email);
        $this->assertEquals('45678912300', $dto->cpf);
        $this->assertEquals('tecnologia2024', $dto->password);
        $this->assertEquals('Arquiteto de Software', $dto->job_title);
        $this->assertEquals('1987-12-05', $dto->birth_date);
        $this->assertEquals('01452-002', $dto->postal_code);
        $this->assertEquals('Alameda Santos, 2000 - Jardins', $dto->address);
        $this->assertEquals('admin', $dto->role);
        $this->assertEquals(12, $dto->updated_by);
    }

    public function test_creates_dto_without_password()
    {
        $dto = new UpdateUserDto(
            name: 'Juliana Ferreira',
            email: 'juliana.ferreira@marketing.com',
            cpf: '65432198700'
        );

        $this->assertEquals('Juliana Ferreira', $dto->name);
        $this->assertEquals('juliana.ferreira@marketing.com', $dto->email);
        $this->assertEquals('65432198700', $dto->cpf);
        $this->assertNull($dto->password);
        $this->assertNull($dto->job_title);
        $this->assertNull($dto->birth_date);
        $this->assertNull($dto->postal_code);
        $this->assertNull($dto->address);
        $this->assertNull($dto->role);
        $this->assertNull($dto->updated_by);
    }

    public function test_create_from_request_with_all_fields()
    {
        $requestData = [
            'name' => 'Diego Alves Pereira',
            'email' => 'diego.pereira@logistica.com',
            'cpf' => '222.333.444-55',
            'password' => 'logistica2024',
            'job_title' => 'Coordenador de Operações',
            'birth_date' => '1983-06-18',
            'postal_code' => '04094-050',
            'address' => 'Rua Domingos de Morais, 1000 - Vila Mariana',
            'role' => 'admin'
        ];

        $request = new UpdateUserRequest($requestData);
        
        $dto = UpdateUserDto::createFromRequest($request);

        $this->assertEquals('Diego Alves Pereira', $dto->name);
        $this->assertEquals('diego.pereira@logistica.com', $dto->email);
        $this->assertEquals('22233344455', $dto->cpf); // CPF sem formatação
        $this->assertEquals('logistica2024', $dto->password);
        $this->assertEquals('Coordenador de Operações', $dto->job_title);
        $this->assertEquals('1983-06-18', $dto->birth_date);
        $this->assertEquals('04094-050', $dto->postal_code);
        $this->assertEquals('Rua Domingos de Morais, 1000 - Vila Mariana', $dto->address);
        $this->assertEquals('admin', $dto->role);
    }

    public function test_create_from_request_with_default_role()
    {
        $requestData = [
            'name' => 'Camila Rodrigues',
            'email' => 'camila.rodrigues@educacao.com',
            'cpf' => '777.888.999-00',
            'password' => 'educacao2024',
            'job_title' => 'Coordenadora Pedagógica',
            'birth_date' => '1990-02-14',
            'postal_code' => '01223-001',
            'address' => 'Rua da Consolação, 1500 - Consolação'
        ];

        $request = new UpdateUserRequest($requestData);
        
        $dto = UpdateUserDto::createFromRequest($request);

        $this->assertEquals(UserRoleEnum::EMPLOYEE->value, $dto->role);
    }

    public function test_create_from_request_with_authenticated_user()
    {
        $expectedUserId = 25;
        $requestData = [
            'name' => 'Thiago Santos',
            'email' => 'thiago.santos@inovacao.com',
            'cpf' => '444.555.666-77',
            'password' => 'inovacao2024'
        ];
        $request = new class($requestData, $expectedUserId) extends \App\Http\Requests\UpdateUserRequest {
            private $fakeUserId;
            public function __construct($data, $fakeUserId) {
                parent::__construct($data);
                $this->fakeUserId = $fakeUserId;
            }
            public function user($guard = null) { return (object)['id' => $this->fakeUserId]; }
        };
        $dto = UpdateUserDto::createFromRequest($request);
        $this->assertEquals($expectedUserId, $dto->updated_by);
    }

    public function test_to_array_with_password()
    {
        $dto = new UpdateUserDto(
            name: 'Vanessa Oliveira',
            email: 'vanessa.oliveira@design.com',
            cpf: '88899900011',
            password: 'design2024',
            job_title: 'Designer UX/UI',
            birth_date: '1994-08-25',
            postal_code: '05433-000',
            address: 'Rua Teodoro Sampaio, 800 - Pinheiros',
            role: 'admin'
        );

        $array = $dto->toArray();

        $expected = [
            'name' => 'Vanessa Oliveira',
            'email' => 'vanessa.oliveira@design.com',
            'cpf' => '88899900011',
            'password' => 'design2024',
            'job_title' => 'Designer UX/UI',
            'birth_date' => '1994-08-25',
            'postal_code' => '05433-000',
            'address' => 'Rua Teodoro Sampaio, 800 - Pinheiros',
            'role' => 'admin',
        ];

        $this->assertEquals($expected, $array);
    }

    public function test_to_array_without_password()
    {
        $dto = new UpdateUserDto(
            name: 'Marcelo Silva',
            email: 'marcelo.silva@consultoria.com',
            cpf: '11122233344',
            job_title: 'Consultor Senior',
            birth_date: '1981-03-10',
            postal_code: '04567-890',
            address: 'Av. Brigadeiro Faria Lima, 3000 - Itaim Bibi',
            role: 'admin'
        );

        $array = $dto->toArray();

        $expected = [
            'name' => 'Marcelo Silva',
            'email' => 'marcelo.silva@consultoria.com',
            'cpf' => '11122233344',
            'job_title' => 'Consultor Senior',
            'birth_date' => '1981-03-10',
            'postal_code' => '04567-890',
            'address' => 'Av. Brigadeiro Faria Lima, 3000 - Itaim Bibi',
            'role' => 'admin',
        ];

        $this->assertEquals($expected, $array);
        $this->assertArrayNotHasKey('password', $array);
    }

    public function test_to_array_with_minimal_properties()
    {
        $dto = new UpdateUserDto(
            name: 'Gabriela Costa',
            email: 'gabriela.costa@startup.com',
            cpf: '55566677788'
        );

        $array = $dto->toArray();

        $expected = [
            'name' => 'Gabriela Costa',
            'email' => 'gabriela.costa@startup.com',
            'cpf' => '55566677788',
            'job_title' => null,
            'birth_date' => null,
            'postal_code' => null,
            'address' => null,
            'role' => null,
        ];

        $this->assertEquals($expected, $array);
        $this->assertArrayNotHasKey('password', $array);
    }
} 