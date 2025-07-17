<?php

namespace Tests\Unit;

use App\Exceptions\DuplicateClockInException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class DuplicateClockInExceptionTest extends TestCase
{
    public function test_exception_has_default_message()
    {
        $exception = new DuplicateClockInException();
        
        $this->assertEquals('Já existe um registro de ponto neste momento.', $exception->getMessage());
    }

    public function test_exception_accepts_custom_message()
    {
        $customMessage = 'Registro duplicado detectado!';
        $exception = new DuplicateClockInException($customMessage);
        
        $this->assertEquals($customMessage, $exception->getMessage());
    }

    public function test_exception_uses_auth_id_when_user_id_not_provided()
    {
        $expectedUserId = 123;
        Auth::shouldReceive('id')->once()->andReturn($expectedUserId);
        
        $exception = new DuplicateClockInException();

        Log::shouldReceive('warning')
            ->once()
            ->with('Tentativa de registro duplicado', [
                'user_id' => $expectedUserId,
                'message' => 'Já existe um registro de ponto neste momento.'
            ]);
        
        $exception->report();
    }

    public function test_exception_uses_provided_user_id()
    {
        $customUserId = 123;
        $exception = new DuplicateClockInException('Test message', $customUserId);
        
        Log::shouldReceive('warning')
            ->once()
            ->with('Tentativa de registro duplicado', [
                'user_id' => $customUserId,
                'message' => 'Test message'
            ]);
        
        $exception->report();
    }

    public function test_exception_reports_to_log()
    {
        Log::shouldReceive('warning')
            ->once()
            ->with('Tentativa de registro duplicado', [
                'user_id' => 456,
                'message' => 'Test message'
            ]);

        $exception = new DuplicateClockInException('Test message', 456);
        $exception->report();
    }

    public function test_exception_extends_base_exception()
    {
        $exception = new DuplicateClockInException();
        
        $this->assertInstanceOf(\Exception::class, $exception);
    }
} 