<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class DuplicateClockInException extends Exception
{
    private int $userId;

    public function __construct(string $message = 'Já existe um registro de ponto neste momento.', ?int $userId = null)
    {
        parent::__construct($message);
        $this->userId = $userId ?? Auth::id();
    }

    public function report(): void
    {
        Log::warning('Tentativa de registro duplicado', [
            'user_id' => $this->userId,
            'message' => $this->getMessage()
        ]);
    }
} 