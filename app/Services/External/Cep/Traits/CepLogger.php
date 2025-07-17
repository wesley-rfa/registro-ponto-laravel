<?php

namespace App\Services\External\Cep\Traits;

use Illuminate\Support\Facades\Log;

trait CepLogger
{
    protected function logCep(string $level, string $message, array $context = []): void
    {
        Log::channel('cep')->$level($message, $context);
    }

    protected function logCepInfo(string $message, array $context = []): void
    {
        $this->logCep('info', $message, $context);
    }

    protected function logCepWarning(string $message, array $context = []): void
    {
        $this->logCep('warning', $message, $context);
    }

    protected function logCepError(string $message, array $context = []): void
    {
        $this->logCep('error', $message, $context);
    }

    protected function logCepDebug(string $message, array $context = []): void
    {
        $this->logCep('debug', $message, $context);
    }
} 