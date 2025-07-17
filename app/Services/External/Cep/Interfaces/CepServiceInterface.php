<?php

namespace App\Services\External\Cep\Interfaces;

use App\Services\External\Cep\Dtos\CepResponseDto;

interface CepServiceInterface
{
    public function searchByCep(string $cep): ?CepResponseDto;
    
    public function isAvailable(): bool;
    
    public function getServiceName(): string;
} 