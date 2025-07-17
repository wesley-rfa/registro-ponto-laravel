<?php

namespace Tests\Unit\Services\External\Cep;

use App\Services\External\Cep\CepService;
use App\Services\External\Cep\CepServiceFactory;
use App\Services\External\Cep\Dtos\CepResponseDto;
use InvalidArgumentException;
use Tests\TestCase;

class CepServiceUnitTest extends TestCase
{
    private CepService $cepService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cepService = new CepService();
    }

    public function test_constructor_initializes_services()
    {
        $cepService = new CepService();

        $this->assertInstanceOf(CepService::class, $cepService);
    }

    public function test_clean_cep_removes_non_numeric_characters()
    {
        $testCases = [
            '01001-000' => '01001000',
            '01001.000' => '01001000',
            '01001 000' => '01001000',
            '01001000' => '01001000',
            'abc01001-000def' => '01001000',
        ];

        foreach ($testCases as $input => $expected) {
            $result = $this->invokePrivateMethod($this->cepService, 'cleanCep', [$input]);
            $this->assertEquals($expected, $result);
        }
    }

    public function test_search_by_cep_with_service_throws_exception_for_invalid_service()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Serviço invalid_service não encontrado');

        $this->cepService->searchByCepWithService('01001000', 'invalid_service');
    }

    public function test_search_by_cep_with_service_returns_null_when_service_fails()
    {
        $mockService = $this->createMock(\App\Services\External\Cep\Interfaces\CepServiceInterface::class);
        $mockService->method('searchByCep')->willReturn(null);

        $reflection = new \ReflectionClass($this->cepService);
        $servicesProperty = $reflection->getProperty('services');
        $servicesProperty->setAccessible(true);
        $services = $servicesProperty->getValue($this->cepService);
        $services[CepServiceFactory::VIA_CEP] = $mockService;
        $servicesProperty->setValue($this->cepService, $services);

        $result = $this->cepService->searchByCepWithService('01001000', CepServiceFactory::VIA_CEP);

        $this->assertNull($result);
    }

    public function test_search_by_cep_with_service_returns_dto_when_service_succeeds()
    {
        $expectedDto = new CepResponseDto(
            cep: '01001000',
            endereco: 'Praça da Sé',
            cidade: 'São Paulo',
            estado: 'SP'
        );

        $mockService = $this->createMock(\App\Services\External\Cep\Interfaces\CepServiceInterface::class);
        $mockService->method('searchByCep')->willReturn($expectedDto);

        $reflection = new \ReflectionClass($this->cepService);
        $servicesProperty = $reflection->getProperty('services');
        $servicesProperty->setAccessible(true);
        $services = $servicesProperty->getValue($this->cepService);
        $services[CepServiceFactory::VIA_CEP] = $mockService;
        $servicesProperty->setValue($this->cepService, $services);

        $result = $this->cepService->searchByCepWithService('01001000', CepServiceFactory::VIA_CEP);

        $this->assertInstanceOf(CepResponseDto::class, $result);
        $this->assertEquals($expectedDto->cep, $result->cep);
        $this->assertEquals($expectedDto->endereco, $result->endereco);
    }

    public function test_check_availability_returns_correct_structure()
    {
        $mockService = $this->createMock(\App\Services\External\Cep\Interfaces\CepServiceInterface::class);
        $mockService->method('isAvailable')->willReturn(true);
        $mockService->method('getServiceName')->willReturn('Test Service');

        $reflection = new \ReflectionClass($this->cepService);
        $servicesProperty = $reflection->getProperty('services');
        $servicesProperty->setAccessible(true);
        $services = [
            CepServiceFactory::VIA_CEP => $mockService,
            CepServiceFactory::CORREIOS => $mockService
        ];
        $servicesProperty->setValue($this->cepService, $services);

        $result = $this->cepService->checkAvailability();

        $this->assertIsArray($result);
        $this->assertArrayHasKey(CepServiceFactory::VIA_CEP, $result);
        $this->assertArrayHasKey(CepServiceFactory::CORREIOS, $result);
        
        foreach ($result as $serviceInfo) {
            $this->assertArrayHasKey('disponivel', $serviceInfo);
            $this->assertArrayHasKey('nome', $serviceInfo);
            $this->assertIsBool($serviceInfo['disponivel']);
            $this->assertIsString($serviceInfo['nome']);
        }
    }

    public function test_get_statistics_returns_correct_structure()
    {
        $result = $this->cepService->getStatistics();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('servicos_disponiveis', $result);
        $this->assertArrayHasKey('total_servicos', $result);
        $this->assertArrayHasKey('ordem_prioridade', $result);
        
        $this->assertIsInt($result['servicos_disponiveis']);
        $this->assertIsInt($result['total_servicos']);
        $this->assertIsArray($result['ordem_prioridade']);
        
        $this->assertGreaterThanOrEqual(0, $result['servicos_disponiveis']);
        $this->assertGreaterThan(0, $result['total_servicos']);
        $this->assertGreaterThan(0, count($result['ordem_prioridade']));
    }

    public function test_get_statistics_servicos_disponiveis_matches_available_services()
    {
        $mockServiceAvailable = $this->createMock(\App\Services\External\Cep\Interfaces\CepServiceInterface::class);
        $mockServiceAvailable->method('isAvailable')->willReturn(true);
        $mockServiceAvailable->method('getServiceName')->willReturn('Available Service');

        $mockServiceUnavailable = $this->createMock(\App\Services\External\Cep\Interfaces\CepServiceInterface::class);
        $mockServiceUnavailable->method('isAvailable')->willReturn(false);
        $mockServiceUnavailable->method('getServiceName')->willReturn('Unavailable Service');

        $reflection = new \ReflectionClass($this->cepService);
        $servicesProperty = $reflection->getProperty('services');
        $servicesProperty->setAccessible(true);
        $services = [
            CepServiceFactory::VIA_CEP => $mockServiceAvailable,
            CepServiceFactory::CORREIOS => $mockServiceUnavailable
        ];
        $servicesProperty->setValue($this->cepService, $services);

        $result = $this->cepService->getStatistics();

        $this->assertEquals(1, $result['servicos_disponiveis']);
        $this->assertEquals(2, $result['total_servicos']);
    }

    public function test_search_by_cep_fallback_works_when_first_service_fails()
    {
        $expectedDto = new CepResponseDto(
            cep: '01001000',
            endereco: 'Praça da Sé',
            cidade: 'São Paulo',
            estado: 'SP'
        );

        $mockService1 = $this->createMock(\App\Services\External\Cep\Interfaces\CepServiceInterface::class);
        $mockService1->method('isAvailable')->willReturn(true);
        $mockService1->method('searchByCep')->willReturn(null);

        $mockService2 = $this->createMock(\App\Services\External\Cep\Interfaces\CepServiceInterface::class);
        $mockService2->method('isAvailable')->willReturn(true);
        $mockService2->method('searchByCep')->willReturn($expectedDto);

        $reflection = new \ReflectionClass($this->cepService);
        $servicesProperty = $reflection->getProperty('services');
        $servicesProperty->setAccessible(true);
        $services = [
            CepServiceFactory::VIA_CEP => $mockService1,
            CepServiceFactory::CORREIOS => $mockService2
        ];
        $servicesProperty->setValue($this->cepService, $services);

        $result = $this->cepService->searchByCep('01001000');

        $this->assertInstanceOf(CepResponseDto::class, $result);
        $this->assertEquals($expectedDto->cep, $result->cep);
    }

    public function test_search_by_cep_returns_null_when_all_services_fail()
    {
        $mockService1 = $this->createMock(\App\Services\External\Cep\Interfaces\CepServiceInterface::class);
        $mockService1->method('isAvailable')->willReturn(true);
        $mockService1->method('searchByCep')->willReturn(null);

        $mockService2 = $this->createMock(\App\Services\External\Cep\Interfaces\CepServiceInterface::class);
        $mockService2->method('isAvailable')->willReturn(true);
        $mockService2->method('searchByCep')->willReturn(null);

        $reflection = new \ReflectionClass($this->cepService);
        $servicesProperty = $reflection->getProperty('services');
        $servicesProperty->setAccessible(true);
        $services = [
            CepServiceFactory::VIA_CEP => $mockService1,
            CepServiceFactory::CORREIOS => $mockService2
        ];
        $servicesProperty->setValue($this->cepService, $services);

        $result = $this->cepService->searchByCep('01001000');

        $this->assertNull($result);
    }

    public function test_search_by_cep_skips_unavailable_services()
    {
        $expectedDto = new CepResponseDto(
            cep: '01001000',
            endereco: 'Praça da Sé',
            cidade: 'São Paulo',
            estado: 'SP'
        );

        $mockService1 = $this->createMock(\App\Services\External\Cep\Interfaces\CepServiceInterface::class);
        $mockService1->method('isAvailable')->willReturn(false);
        $mockService1->method('searchByCep')->willReturn(null);

        $mockService2 = $this->createMock(\App\Services\External\Cep\Interfaces\CepServiceInterface::class);
        $mockService2->method('isAvailable')->willReturn(true);
        $mockService2->method('searchByCep')->willReturn($expectedDto);

        $reflection = new \ReflectionClass($this->cepService);
        $servicesProperty = $reflection->getProperty('services');
        $servicesProperty->setAccessible(true);
        $services = [
            CepServiceFactory::VIA_CEP => $mockService1,
            CepServiceFactory::CORREIOS => $mockService2
        ];
        $servicesProperty->setValue($this->cepService, $services);

        $result = $this->cepService->searchByCep('01001000');

        $this->assertInstanceOf(CepResponseDto::class, $result);
        $this->assertEquals($expectedDto->cep, $result->cep);
    }

    public function test_search_by_cep_handles_service_exceptions()
    {
        $expectedDto = new CepResponseDto(
            cep: '01001000',
            endereco: 'Praça da Sé',
            cidade: 'São Paulo',
            estado: 'SP'
        );

        $mockService1 = $this->createMock(\App\Services\External\Cep\Interfaces\CepServiceInterface::class);
        $mockService1->method('isAvailable')->willReturn(true);
        $mockService1->method('searchByCep')->willThrowException(new \Exception('Service error'));

        $mockService2 = $this->createMock(\App\Services\External\Cep\Interfaces\CepServiceInterface::class);
        $mockService2->method('isAvailable')->willReturn(true);
        $mockService2->method('searchByCep')->willReturn($expectedDto);

        $reflection = new \ReflectionClass($this->cepService);
        $servicesProperty = $reflection->getProperty('services');
        $servicesProperty->setAccessible(true);
        $services = [
            CepServiceFactory::VIA_CEP => $mockService1,
            CepServiceFactory::CORREIOS => $mockService2
        ];
        $servicesProperty->setValue($this->cepService, $services);

        $result = $this->cepService->searchByCep('01001000');

        $this->assertInstanceOf(CepResponseDto::class, $result);
        $this->assertEquals($expectedDto->cep, $result->cep);
    }

    private function invokePrivateMethod($object, string $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass($object);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }
} 