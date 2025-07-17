<?php

namespace App\Services\External\Cep;

use App\Services\External\Cep\Dtos\CepResponseDto;
use App\Services\External\Cep\Traits\CepLogger;

class CepService
{
    use CepLogger;

    private array $services;

    public function __construct()
    {
        $this->services = CepServiceFactory::getAllServices();
    }

    public function searchByCep(string $cep): ?CepResponseDto
    {
        $cepLimpo = $this->cleanCep($cep);
        
        foreach (CepServiceFactory::getServicesByPriority() as $tipoServico) {
            $service = $this->services[$tipoServico];
            
            if (!$service->isAvailable()) {
                $this->logCepWarning("Serviço {$tipoServico} não está disponível, tentando próximo...");
                continue;
            }
            
            try {
                $resultado = $service->searchByCep($cepLimpo);
                
                if ($resultado) {
                    return $resultado;
                }
                
                $this->logCepInfo("CEP não encontrado no serviço {$tipoServico}, tentando próximo...");
                
            } catch (\Exception $e) {
                $this->logCepError("Erro no serviço {$tipoServico}", [
                    'cep' => $cepLimpo,
                    'error' => $e->getMessage()
                ]);
                continue;
            }
        }
        
        $this->logCepWarning('CEP não encontrado em nenhum serviço', ['cep' => $cepLimpo]);
        return null;
    }

    public function searchByCepWithService(string $cep, string $tipoServico): ?CepResponseDto
    {
        if (!isset($this->services[$tipoServico])) {
            throw new \InvalidArgumentException("Serviço {$tipoServico} não encontrado");
        }
        
        $service = $this->services[$tipoServico];
        $cepLimpo = $this->cleanCep($cep);
        
        $this->logCepInfo("Buscando CEP no serviço específico {$tipoServico}", ['cep' => $cepLimpo]);
        
        return $service->searchByCep($cepLimpo);
    }

    public function checkAvailability(): array
    {
        $status = [];
        
        foreach ($this->services as $tipo => $service) {
            $status[$tipo] = [
                'disponivel' => $service->isAvailable(),
                'nome' => $service->getServiceName()
            ];
        }
        
        return $status;
    }

    public function getStatistics(): array
    {
        return [
            'servicos_disponiveis' => count(array_filter($this->services, fn($s) => $s->isAvailable())),
            'total_servicos' => count($this->services),
            'ordem_prioridade' => CepServiceFactory::getServicesByPriority()
        ];
    }

    private function cleanCep(string $cep): string
    {
        return preg_replace('/[^0-9]/', '', $cep);
    }
} 