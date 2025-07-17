<?php

namespace Tests\Feature;

use App\Services\External\Cep\CepService;
use App\Services\External\Cep\CepServiceFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CepServiceTest extends TestCase
{
    use RefreshDatabase;

    private CepService $cepService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cepService = new CepService();
    }

    public function test_can_search_cep_with_fallback()
    {
        $cep = '01001000';
        
        $resultado = $this->cepService->searchByCep($cep);
        
        $this->assertNotNull($resultado);
        $this->assertEquals($cep, $resultado->cep);
        $this->assertNotEmpty($resultado->endereco);
        $this->assertNotEmpty($resultado->cidade);
        $this->assertNotEmpty($resultado->estado);
    }

    public function test_can_search_cep_with_specific_service()
    {
        $cep = '01001000';
        $servico = CepServiceFactory::VIA_CEP;
        
        $resultado = $this->cepService->searchByCepWithService($cep, $servico);
        
        $this->assertNotNull($resultado);
        $this->assertEquals($cep, $resultado->cep);
    }

    public function test_viacep_service_works()
    {
        $cep = '01001000';
        $servico = CepServiceFactory::VIA_CEP;
        
        $resultado = $this->cepService->searchByCepWithService($cep, $servico);
        
        $this->assertNotNull($resultado);
        $this->assertEquals($cep, $resultado->cep);
        $this->assertNotEmpty($resultado->endereco);
        $this->assertNotEmpty($resultado->cidade);
        $this->assertNotEmpty($resultado->estado);
    }

    public function test_correios_service_works()
    {
        $cep = '01001000';
        $servico = CepServiceFactory::CORREIOS;
        
        $resultado = $this->cepService->searchByCepWithService($cep, $servico);
        
        $this->assertNotNull($resultado);
        $this->assertEquals($cep, $resultado->cep);
        $this->assertNotEmpty($resultado->endereco);
        $this->assertNotEmpty($resultado->cidade);
        $this->assertNotEmpty($resultado->estado);
    }

    public function test_returns_null_for_invalid_cep()
    {
        $cep = '00000000';
        
        $resultado = $this->cepService->searchByCep($cep);
        
        $this->assertNull($resultado);
    }

    public function test_checks_service_availability()
    {
        $disponibilidade = $this->cepService->checkAvailability();
        
        $this->assertIsArray($disponibilidade);
        $this->assertArrayHasKey(CepServiceFactory::VIA_CEP, $disponibilidade);
        $this->assertArrayHasKey(CepServiceFactory::CORREIOS, $disponibilidade);
        
        foreach ($disponibilidade as $servico) {
            $this->assertArrayHasKey('disponivel', $servico);
            $this->assertArrayHasKey('nome', $servico);
            $this->assertIsBool($servico['disponivel']);
            $this->assertIsString($servico['nome']);
        }
    }

    public function test_returns_statistics()
    {
        $estatisticas = $this->cepService->getStatistics();
        
        $this->assertIsArray($estatisticas);
        $this->assertArrayHasKey('servicos_disponiveis', $estatisticas);
        $this->assertArrayHasKey('total_servicos', $estatisticas);
        $this->assertArrayHasKey('ordem_prioridade', $estatisticas);
        
        $this->assertIsInt($estatisticas['servicos_disponiveis']);
        $this->assertIsInt($estatisticas['total_servicos']);
        $this->assertIsArray($estatisticas['ordem_prioridade']);
    }

    public function test_cleans_cep_correctly()
    {
        $ceps = [
            '01001-000' => '01001000',
            '01001.000' => '01001000',
            '01001 000' => '01001000',
            '01001000' => '01001000',
        ];
        
        foreach ($ceps as $cepOriginal => $cepEsperado) {
            $resultado = $this->cepService->searchByCep($cepOriginal);
            if ($resultado) {
                $this->assertEquals($cepEsperado, $resultado->cep);
            }
        }
    }

    public function test_complete_address_returns_formatted_string()
    {
        $cep = '01001000';
        $resultado = $this->cepService->searchByCep($cep);
        
        if ($resultado) {
            $enderecoCompleto = $resultado->getCompleteAddress();
            
            $this->assertIsString($enderecoCompleto);
            $this->assertNotEmpty($enderecoCompleto);
            $this->assertStringContainsString($resultado->endereco, $enderecoCompleto);
        }
    }

    public function test_fallback_works_when_viacep_fails()
    {
        $cep = '01001000';
        
        $resultado = $this->cepService->searchByCep($cep);
        
        $this->assertNotNull($resultado);
        $this->assertEquals($cep, $resultado->cep);
        $this->assertNotEmpty($resultado->endereco);
    }
} 