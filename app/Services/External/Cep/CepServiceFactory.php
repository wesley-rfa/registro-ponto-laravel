<?php

namespace App\Services\External\Cep;

use App\Services\External\Cep\Interfaces\CepServiceInterface;
use InvalidArgumentException;

class CepServiceFactory
{
    public const VIA_CEP = 'viacep';
    public const CORREIOS = 'correios';

    public static function create(string $tipo): CepServiceInterface
    {
        return match ($tipo) {
            self::VIA_CEP => new ViaCepService(),
            self::CORREIOS => new CorreiosCepService(),
            default => throw new InvalidArgumentException("Tipo de serviço CEP não suportado: {$tipo}")
        };
    }

    public static function getAllServices(): array
    {
        return [
            self::VIA_CEP => new ViaCepService(),
            self::CORREIOS => new CorreiosCepService(),
        ];
    }

    public static function getServicesByPriority(): array
    {
        return [
            self::VIA_CEP,
            self::CORREIOS,
        ];
    }
}
