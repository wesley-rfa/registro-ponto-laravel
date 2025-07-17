<?php

namespace App\Services\External\Cep\Dtos;

class CepResponseDto
{
    public function __construct(
        public readonly string $cep,
        public readonly string $endereco,
        public readonly ?string $bairro = null,
        public readonly ?string $cidade = null,
        public readonly ?string $estado = null,
        public readonly ?string $complemento = null
    ) {}

    public static function create(array $data): ?self
    {
        $endereco = $data['endereco'] ?? $data['logradouro'] ?? '';
        
        if (empty($endereco) && empty($data['cidade'] ?? $data['localidade'] ?? '')) {
            return null;
        }

        return new self(
            cep: $data['cep'] ?? '',
            endereco: $endereco,
            bairro: $data['bairro'] ?? null,
            cidade: $data['cidade'] ?? $data['localidade'] ?? null,
            estado: $data['estado'] ?? $data['uf'] ?? null,
            complemento: $data['complemento'] ?? null
        );
    }

    public function getCompleteAddress(): string
    {
        $partes = array_filter([
            $this->endereco,
            $this->complemento,
            $this->bairro,
            $this->cidade,
            $this->estado
        ]);

        return implode(', ', $partes);
    }
}