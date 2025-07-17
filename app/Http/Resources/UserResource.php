<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'cpf' => \App\Helpers\CpfHelper::format($this->cpf),
            'job_title' => $this->job_title,
            'birth_date' => $this->birth_date?->format('Y-m-d'),
            'postal_code' => $this->postal_code,
            'address' => $this->address,
            'role' => $this->role->value,
        ];
    }
} 