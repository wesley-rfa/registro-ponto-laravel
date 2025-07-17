<?php

namespace App\Services\External\Cep;

use App\Services\External\Cep\Dtos\CepResponseDto;
use App\Services\External\Cep\Interfaces\CepServiceInterface;
use App\Services\External\Cep\Traits\CepLogger;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

abstract class AbstractCepService implements CepServiceInterface
{
    use CepLogger;

    protected PendingRequest $httpClient;
    protected int $timeout;

    public function __construct()
    {
        $this->timeout = $this->getTimeout();
        $this->httpClient = Http::timeout($this->timeout)
            ->withHeaders($this->getHeaders());
    }

    public function searchByCep(string $cep): ?CepResponseDto
    {
        $cepLimpo = $this->cleanCep($cep);
        
        $this->logCepInfo("Iniciando busca no {$this->getServiceName()}", ['cep' => $cepLimpo]);
        
        try {
            $response = $this->makeRequest($cepLimpo);
            
            if (!$this->isResponseSuccessful($response)) {
                $this->logCepWarning('Erro HTTP', [
                    'cep' => $cepLimpo,
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return null;
            }

            $data = $this->parseResponse($response);
            
            if (!$this->validateResponse($data, $cepLimpo)) {
                return null;
            }

            $dto = $this->createDto($data, $cepLimpo);
            
            if (!$dto) {
                $this->logCepWarning('Dados incompletos para criação do DTO', [
                    'cep' => $cepLimpo,
                    'data' => $data
                ]);
                return null;
            }

            $this->logCepInfo("CEP encontrado no {$this->getServiceName()}", [
                'cep' => $cepLimpo,
                'endereco' => $dto->getCompleteAddress()
            ]);

            return $dto;

        } catch (\Exception $e) {
            $this->logCepError("Erro ao buscar CEP no {$this->getServiceName()}", [
                'cep' => $cep,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    public function isAvailable(): bool
    {
        try {
            $testCep = $this->getTestCep();
            $response = $this->httpClient->get($this->getAvailabilityUrl($testCep));
            $isAvailable = $response->successful();
            
            return $isAvailable;
        } catch (\Exception $e) {
            $this->logCepWarning("{$this->getServiceName()} não está disponível", [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    protected function cleanCep(string $cep): string
    {
        return preg_replace('/[^0-9]/', '', $cep);
    }

    protected function logError(string $message, string $cep, $data = null): void
    {
        $this->logCepWarning("{$this->getServiceName()}: {$message}", [
            'cep' => $cep,
            'data' => $data
        ]);
    }

    protected function logSuccess(string $cep, CepResponseDto $dto): void
    {
        $this->logCepInfo("CEP encontrado no {$this->getServiceName()}", [
            'cep' => $cep,
            'endereco' => $dto->getCompleteAddress()
        ]);
    }

    protected function logException(\Exception $e, string $cep): void
    {
        $this->logCepError("Erro ao buscar CEP no {$this->getServiceName()}", [
            'cep' => $cep,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }

    protected function isResponseSuccessful($response): bool
    {
        return $response->successful();
    }

    protected function parseResponse($response): array
    {
        return $response->json();
    }

    protected function validateResponse(array $data, string $cep): bool
    {
        return !empty($data);
    }

    protected function getTestCep(): string
    {
        return '01001000';
    }

    protected function getHeaders(): array
    {
        return [
            'Accept' => 'application/json',
        ];
    }

    abstract protected function getTimeout(): int;
    abstract protected function makeRequest(string $cep);
    abstract protected function getAvailabilityUrl(string $cep): string;
    abstract protected function createDto(array $data, string $cep): ?CepResponseDto;
} 