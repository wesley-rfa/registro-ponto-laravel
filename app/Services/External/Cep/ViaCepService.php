<?php

namespace App\Services\External\Cep;

use App\Services\External\Cep\Dtos\CepResponseDto;

class ViaCepService extends AbstractCepService
{
    private const BASE_URL = 'https://viacep.com.br/ws';

    protected function getTimeout(): int
    {
        return 10;
    }

    protected function makeRequest(string $cep)
    {
        return $this->httpClient->get(self::BASE_URL . "/{$cep}/json");
    }

    protected function getAvailabilityUrl(string $cep): string
    {
        return self::BASE_URL . "/{$cep}/json";
    }

    protected function validateResponse(array $data, string $cep): bool
    {
        if (isset($data['erro']) && $data['erro'] === true) {
            $this->logError('CEP não encontrado', $cep);
            return false;
        }

        if (empty($data['logradouro']) && empty($data['localidade'])) {
            $this->logError('Dados incompletos', $cep, $data);
            return false;
        }

        return true;
    }

    protected function createDto(array $data, string $cep): ?CepResponseDto
    {
        return CepResponseDto::create([
            'cep' => $cep,
            'logradouro' => $data['logradouro'] ?? '',
            'bairro' => $data['bairro'] ?? null,
            'localidade' => $data['localidade'] ?? null,
            'uf' => $data['uf'] ?? null,
            'complemento' => $data['complemento'] ?? null
        ]);
    }

    public function getServiceName(): string
    {
        return 'ViaCEP';
    }
}
