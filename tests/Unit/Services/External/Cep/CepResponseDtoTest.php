<?php

namespace Tests\Unit\Services\External\Cep;

use App\Services\External\Cep\Dtos\CepResponseDto;
use Tests\TestCase;

class CepResponseDtoTest extends TestCase
{
    public function test_can_create_dto_with_constructor()
    {
        $dto = new CepResponseDto(
            cep: '01001000',
            endereco: 'Praça da Sé',
            bairro: 'Sé',
            cidade: 'São Paulo',
            estado: 'SP',
            complemento: 'lado ímpar'
        );

        $this->assertEquals('01001000', $dto->cep);
        $this->assertEquals('Praça da Sé', $dto->endereco);
        $this->assertEquals('Sé', $dto->bairro);
        $this->assertEquals('São Paulo', $dto->cidade);
        $this->assertEquals('SP', $dto->estado);
        $this->assertEquals('lado ímpar', $dto->complemento);
    }

    public function test_can_create_dto_with_minimal_data()
    {
        $dto = new CepResponseDto(
            cep: '01001000',
            endereco: 'Praça da Sé'
        );

        $this->assertEquals('01001000', $dto->cep);
        $this->assertEquals('Praça da Sé', $dto->endereco);
        $this->assertNull($dto->bairro);
        $this->assertNull($dto->cidade);
        $this->assertNull($dto->estado);
        $this->assertNull($dto->complemento);
    }

    public function test_create_static_method_with_viacep_format()
    {
        $data = [
            'cep' => '01001-000',
            'logradouro' => 'Praça da Sé',
            'bairro' => 'Sé',
            'localidade' => 'São Paulo',
            'uf' => 'SP',
            'complemento' => 'lado ímpar'
        ];

        $dto = CepResponseDto::create($data);

        $this->assertInstanceOf(CepResponseDto::class, $dto);
        $this->assertEquals('01001-000', $dto->cep);
        $this->assertEquals('Praça da Sé', $dto->endereco);
        $this->assertEquals('Sé', $dto->bairro);
        $this->assertEquals('São Paulo', $dto->cidade);
        $this->assertEquals('SP', $dto->estado);
        $this->assertEquals('lado ímpar', $dto->complemento);
    }

    public function test_create_static_method_with_correios_format()
    {
        $data = [
            'cep' => '01001000',
            'endereco' => 'Praça da Sé',
            'bairro' => 'Sé',
            'cidade' => 'São Paulo',
            'estado' => 'SP',
            'complemento' => 'lado ímpar'
        ];

        $dto = CepResponseDto::create($data);

        $this->assertInstanceOf(CepResponseDto::class, $dto);
        $this->assertEquals('01001000', $dto->cep);
        $this->assertEquals('Praça da Sé', $dto->endereco);
        $this->assertEquals('Sé', $dto->bairro);
        $this->assertEquals('São Paulo', $dto->cidade);
        $this->assertEquals('SP', $dto->estado);
        $this->assertEquals('lado ímpar', $dto->complemento);
    }

    public function test_create_returns_null_when_no_address_data()
    {
        $data = [
            'cep' => '01001000',
        ];

        $dto = CepResponseDto::create($data);

        $this->assertNull($dto);
    }

    public function test_create_returns_null_when_empty_address_data()
    {
        $data = [
            'cep' => '01001000',
            'endereco' => '',
            'cidade' => ''
        ];

        $dto = CepResponseDto::create($data);

        $this->assertNull($dto);
    }

    public function test_get_complete_address_with_all_fields()
    {
        $dto = new CepResponseDto(
            cep: '01001000',
            endereco: 'Praça da Sé',
            bairro: 'Sé',
            cidade: 'São Paulo',
            estado: 'SP',
            complemento: 'lado ímpar'
        );

        $enderecoCompleto = $dto->getCompleteAddress();

        $this->assertEquals('Praça da Sé, lado ímpar, Sé, São Paulo, SP', $enderecoCompleto);
    }

    public function test_get_complete_address_with_minimal_fields()
    {
        $dto = new CepResponseDto(
            cep: '01001000',
            endereco: 'Praça da Sé',
            cidade: 'São Paulo'
        );

        $enderecoCompleto = $dto->getCompleteAddress();

        $this->assertEquals('Praça da Sé, São Paulo', $enderecoCompleto);
    }

    public function test_get_complete_address_filters_null_values()
    {
        $dto = new CepResponseDto(
            cep: '01001000',
            endereco: 'Praça da Sé',
            bairro: null,
            cidade: 'São Paulo',
            estado: null,
            complemento: null
        );

        $enderecoCompleto = $dto->getCompleteAddress();

        $this->assertEquals('Praça da Sé, São Paulo', $enderecoCompleto);
    }

    public function test_get_complete_address_with_empty_strings()
    {
        $dto = new CepResponseDto(
            cep: '01001000',
            endereco: 'Praça da Sé',
            bairro: '',
            cidade: 'São Paulo',
            estado: '',
            complemento: ''
        );

        $enderecoCompleto = $dto->getCompleteAddress();

        $this->assertEquals('Praça da Sé, São Paulo', $enderecoCompleto);
    }

    public function test_properties_are_readonly()
    {
        $dto = new CepResponseDto(
            cep: '01001000',
            endereco: 'Praça da Sé'
        );

        $this->assertTrue((new \ReflectionProperty($dto, 'cep'))->isReadOnly());
        $this->assertTrue((new \ReflectionProperty($dto, 'endereco'))->isReadOnly());
        $this->assertTrue((new \ReflectionProperty($dto, 'bairro'))->isReadOnly());
        $this->assertTrue((new \ReflectionProperty($dto, 'cidade'))->isReadOnly());
        $this->assertTrue((new \ReflectionProperty($dto, 'estado'))->isReadOnly());
        $this->assertTrue((new \ReflectionProperty($dto, 'complemento'))->isReadOnly());
    }
} 