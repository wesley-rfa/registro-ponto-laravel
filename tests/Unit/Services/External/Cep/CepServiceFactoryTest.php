<?php

namespace Tests\Unit\Services\External\Cep;

use App\Services\External\Cep\CepServiceFactory;
use App\Services\External\Cep\CorreiosCepService;
use App\Services\External\Cep\ViaCepService;
use InvalidArgumentException;
use Tests\TestCase;

class CepServiceFactoryTest extends TestCase
{
    public function test_can_create_viacep_service()
    {
        $service = CepServiceFactory::create(CepServiceFactory::VIA_CEP);

        $this->assertInstanceOf(ViaCepService::class, $service);
    }

    public function test_can_create_correios_service()
    {
        $service = CepServiceFactory::create(CepServiceFactory::CORREIOS);

        $this->assertInstanceOf(CorreiosCepService::class, $service);
    }

    public function test_throws_exception_for_invalid_service_type()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Tipo de serviço CEP não suportado: invalid_service');

        CepServiceFactory::create('invalid_service');
    }

    public function test_get_all_services_returns_all_available_services()
    {
        $services = CepServiceFactory::getAllServices();

        $this->assertIsArray($services);
        $this->assertCount(2, $services);
        $this->assertArrayHasKey(CepServiceFactory::VIA_CEP, $services);
        $this->assertArrayHasKey(CepServiceFactory::CORREIOS, $services);
        $this->assertInstanceOf(ViaCepService::class, $services[CepServiceFactory::VIA_CEP]);
        $this->assertInstanceOf(CorreiosCepService::class, $services[CepServiceFactory::CORREIOS]);
    }

    public function test_get_services_by_priority_returns_priority_order()
    {
        $priority = CepServiceFactory::getServicesByPriority();

        $this->assertIsArray($priority);
        $this->assertCount(2, $priority);
        $this->assertEquals(CepServiceFactory::VIA_CEP, $priority[0]);
        $this->assertEquals(CepServiceFactory::CORREIOS, $priority[1]);
    }

    public function test_constants_are_defined()
    {
        $this->assertEquals('viacep', CepServiceFactory::VIA_CEP);
        $this->assertEquals('correios', CepServiceFactory::CORREIOS);
    }

    public function test_services_are_different_instances()
    {
        $service1 = CepServiceFactory::create(CepServiceFactory::VIA_CEP);
        $service2 = CepServiceFactory::create(CepServiceFactory::VIA_CEP);

        $this->assertNotSame($service1, $service2);
    }

    public function test_get_all_services_returns_different_instances()
    {
        $services1 = CepServiceFactory::getAllServices();
        $services2 = CepServiceFactory::getAllServices();

        $this->assertNotSame($services1[CepServiceFactory::VIA_CEP], $services2[CepServiceFactory::VIA_CEP]);
        $this->assertNotSame($services1[CepServiceFactory::CORREIOS], $services2[CepServiceFactory::CORREIOS]);
    }

    public function test_priority_order_matches_service_creation_order()
    {
        $priority = CepServiceFactory::getServicesByPriority();
        $services = CepServiceFactory::getAllServices();

        foreach ($priority as $serviceType) {
            $this->assertArrayHasKey($serviceType, $services);
        }
    }

    public function test_all_service_types_in_priority_are_valid()
    {
        $priority = CepServiceFactory::getServicesByPriority();

        foreach ($priority as $serviceType) {
            $this->assertContains($serviceType, [CepServiceFactory::VIA_CEP, CepServiceFactory::CORREIOS]);
        }
    }
} 