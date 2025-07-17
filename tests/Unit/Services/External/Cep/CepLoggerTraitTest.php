<?php

namespace Tests\Unit\Services\External\Cep;

use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class CepLoggerTraitTest extends TestCase
{
    private TestCepLogger $logger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->logger = new TestCepLogger();
    }

    public function test_log_cep_info()
    {
        Log::shouldReceive('channel')
            ->once()
            ->with('cep')
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once()
            ->with('Test info message', ['context' => 'test']);

        $this->logger->testLogCepInfo('Test info message', ['context' => 'test']);
    }

    public function test_log_cep_warning()
    {
        Log::shouldReceive('channel')
            ->once()
            ->with('cep')
            ->andReturnSelf();

        Log::shouldReceive('warning')
            ->once()
            ->with('Test warning message', ['context' => 'test']);

        $this->logger->testLogCepWarning('Test warning message', ['context' => 'test']);
    }

    public function test_log_cep_error()
    {
        Log::shouldReceive('channel')
            ->once()
            ->with('cep')
            ->andReturnSelf();

        Log::shouldReceive('error')
            ->once()
            ->with('Test error message', ['context' => 'test']);

        $this->logger->testLogCepError('Test error message', ['context' => 'test']);
    }

    public function test_log_cep_debug()
    {
        Log::shouldReceive('channel')
            ->once()
            ->with('cep')
            ->andReturnSelf();

        Log::shouldReceive('debug')
            ->once()
            ->with('Test debug message', ['context' => 'test']);

        $this->logger->testLogCepDebug('Test debug message', ['context' => 'test']);
    }

    public function test_log_cep_with_empty_context()
    {
        Log::shouldReceive('channel')
            ->once()
            ->with('cep')
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once()
            ->with('Test message', []);

        $this->logger->testLogCepInfo('Test message');
    }

    public function test_log_cep_with_complex_context()
    {
        $context = [
            'cep' => '01001000',
            'service' => 'viacep',
            'response_time' => 150,
            'status' => 'success'
        ];

        Log::shouldReceive('channel')
            ->once()
            ->with('cep')
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once()
            ->with('CEP encontrado', $context);

        $this->logger->testLogCepInfo('CEP encontrado', $context);
    }

    public function test_log_cep_uses_cep_channel()
    {
        Log::shouldReceive('channel')
            ->once()
            ->with('cep')
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once()
            ->with('Test', []);

        $this->logger->testLogCep('info', 'Test');
    }

    public function test_all_log_levels_are_supported()
    {
        $levels = ['info', 'warning', 'error', 'debug'];

        foreach ($levels as $level) {
            Log::shouldReceive('channel')
                ->once()
                ->with('cep')
                ->andReturnSelf();

            Log::shouldReceive($level)
                ->once()
                ->with("Test {$level} message", []);

            $this->logger->testLogCep($level, "Test {$level} message");
        }
    }
}

class TestCepLogger
{
    use \App\Services\External\Cep\Traits\CepLogger;

    public function testLogCep(string $level, string $message, array $context = []): void
    {
        $this->logCep($level, $message, $context);
    }

    public function testLogCepInfo(string $message, array $context = []): void
    {
        $this->logCepInfo($message, $context);
    }

    public function testLogCepWarning(string $message, array $context = []): void
    {
        $this->logCepWarning($message, $context);
    }

    public function testLogCepError(string $message, array $context = []): void
    {
        $this->logCepError($message, $context);
    }

    public function testLogCepDebug(string $message, array $context = []): void
    {
        $this->logCepDebug($message, $context);
    }
} 