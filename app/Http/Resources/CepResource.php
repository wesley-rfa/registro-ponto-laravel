<?php

namespace App\Http\Resources;

use App\Services\External\Cep\Dtos\CepResponseDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CepResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var CepResponseDto $this */
        return [
            'cep' => $this->cep,
            'address' => $this->getCompleteAddress(),
        ];
    }
} 