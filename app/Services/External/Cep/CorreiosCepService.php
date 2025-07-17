<?php

namespace App\Services\External\Cep;

use App\Services\External\Cep\Dtos\CepResponseDto;

class CorreiosCepService extends AbstractCepService
{
    private const BASE_URL = 'https://cep.awesomeapi.com.br/json';

    protected function getTimeout(): int
    {
        return 15;
    }

    protected function makeRequest(string $cep)
    {
        return $this->httpClient->get(self::BASE_URL . "/{$cep}");
    }

    protected function getAvailabilityUrl(string $cep): string
    {
        return self::BASE_URL . "/{$cep}";
    }

    protected function validateResponse(array $data, string $cep): bool
    {
        if (isset($data['status']) && $data['status'] === 'error') {
            $this->logError('CEP não encontrado', $cep);
            return false;
        }

        if (empty($data['address']) && empty($data['city'])) {
            $this->logError('Dados incompletos', $cep, $data);
            return false;
        }

        return true;
    }

    protected function createDto(array $data, string $cep): ?CepResponseDto
    {
        return CepResponseDto::create([
            'cep' => $cep,
            'endereco' => $data['address'] ?? $data['logradouro'] ?? '',
            'bairro' => $data['district'] ?? $data['bairro'] ?? null,
            'cidade' => $data['city'] ?? $data['localidade'] ?? null,
            'estado' => $data['state'] ?? $data['uf'] ?? null,
            'complemento' => $data['complement'] ?? $data['complemento'] ?? null
        ]);
    }

    public function getServiceName(): string
    {
        return 'Correios';
    }
}
