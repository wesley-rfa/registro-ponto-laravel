<?php

namespace Tests\Unit\Services\External\Cep;

use App\Services\External\Cep\ViaCepService;
use App\Services\External\Cep\Dtos\CepResponseDto;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Tests\TestCase;

class ViaCepServiceTest extends TestCase
{
    private ViaCepService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ViaCepService();
    }

    public function test_get_timeout_returns_correct_value()
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('getTimeout');
        $method->setAccessible(true);

        $timeout = $method->invoke($this->service);

        $this->assertEquals(10, $timeout);
    }

    public function test_get_service_name_returns_correct_value()
    {
        $serviceName = $this->service->getServiceName();

        $this->assertEquals('ViaCEP', $serviceName);
    }

    public function test_get_availability_url_returns_correct_format()
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('getAvailabilityUrl');
        $method->setAccessible(true);

        $url = $method->invoke($this->service, '01001000');

        $this->assertEquals('https://viacep.com.br/ws/01001000/json', $url);
    }

    public function test_make_request_returns_correct_response()
    {
        $cep = '01001000';
        $expectedUrl = 'https://viacep.com.br/ws/01001000/json';

        $mockResponse = $this->createMock(Response::class);
        $mockHttpClient = $this->createMock(PendingRequest::class);
        $mockHttpClient->expects($this->once())
            ->method('get')
            ->with($expectedUrl)
            ->willReturn($mockResponse);

        $reflection = new \ReflectionClass($this->service);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($this->service, $mockHttpClient);

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('makeRequest');
        $method->setAccessible(true);

        $result = $method->invoke($this->service, $cep);

        $this->assertSame($mockResponse, $result);
    }

    public function test_create_dto_with_valid_viacep_data()
    {
        $data = [
            'logradouro' => 'Praça da Sé',
            'bairro' => 'Sé',
            'localidade' => 'São Paulo',
            'uf' => 'SP',
            'complemento' => 'lado ímpar'
        ];

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('createDto');
        $method->setAccessible(true);

        $dto = $method->invoke($this->service, $data, '01001000');

        $this->assertInstanceOf(CepResponseDto::class, $dto);
        $this->assertEquals('01001000', $dto->cep);
        $this->assertEquals('Praça da Sé', $dto->endereco);
        $this->assertEquals('Sé', $dto->bairro);
        $this->assertEquals('São Paulo', $dto->cidade);
        $this->assertEquals('SP', $dto->estado);
        $this->assertEquals('lado ímpar', $dto->complemento);
    }

    public function test_create_dto_with_invalid_data_returns_null()
    {
        $data = [
            'erro' => true
        ];

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('createDto');
        $method->setAccessible(true);

        $dto = $method->invoke($this->service, $data, '01001000');

        $this->assertNull($dto);
    }

    public function test_validate_response_with_valid_data()
    {
        $data = [
            'cep' => '01001-000',
            'logradouro' => 'Praça da Sé'
        ];

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('validateResponse');
        $method->setAccessible(true);

        $result = $method->invoke($this->service, $data, '01001000');

        $this->assertTrue($result);
    }

    public function test_validate_response_with_error_data()
    {
        $data = [
            'erro' => true
        ];

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('validateResponse');
        $method->setAccessible(true);

        $result = $method->invoke($this->service, $data, '01001000');

        $this->assertFalse($result);
    }

    public function test_validate_response_with_empty_data()
    {
        $data = [];

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('validateResponse');
        $method->setAccessible(true);

        $result = $method->invoke($this->service, $data, '01001000');

        $this->assertFalse($result);
    }

    public function test_search_by_cep_with_viacep_success()
    {
        $cep = '01001000';
        $expectedData = [
            'logradouro' => 'Praça da Sé',
            'bairro' => 'Sé',
            'localidade' => 'São Paulo',
            'uf' => 'SP'
        ];

        $mockResponse = $this->createMock(Response::class);
        $mockResponse->method('successful')->willReturn(true);
        $mockResponse->method('json')->willReturn($expectedData);

        $mockHttpClient = $this->createMock(PendingRequest::class);
        $mockHttpClient->method('get')->willReturn($mockResponse);

        $reflection = new \ReflectionClass($this->service);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($this->service, $mockHttpClient);

        $result = $this->service->searchByCep($cep);

        $this->assertInstanceOf(CepResponseDto::class, $result);
        $this->assertEquals('01001000', $result->cep);
        $this->assertEquals('Praça da Sé', $result->endereco);
        $this->assertEquals('Sé', $result->bairro);
        $this->assertEquals('São Paulo', $result->cidade);
        $this->assertEquals('SP', $result->estado);
    }

    public function test_search_by_cep_with_viacep_error()
    {
        $cep = '00000000';
        $errorData = ['erro' => true];

        $mockResponse = $this->createMock(Response::class);
        $mockResponse->method('successful')->willReturn(true);
        $mockResponse->method('json')->willReturn($errorData);

        $mockHttpClient = $this->createMock(PendingRequest::class);
        $mockHttpClient->method('get')->willReturn($mockResponse);

        $reflection = new \ReflectionClass($this->service);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($this->service, $mockHttpClient);

        $result = $this->service->searchByCep($cep);

        $this->assertNull($result);
    }
} 