<?php

namespace Tests\Unit;

use App\Rules\CepRule;
use Illuminate\Validation\Validator;
use Tests\TestCase;

class CepRuleTest extends TestCase
{
    private CepRule $cepRule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cepRule = new CepRule();
    }

    public function test_valid_ceps(): void
    {
        $validCeps = [
            '01001-000',
            '01001.000',
            '01001 000',
            '01001000',
            '12345-678',
            '98765-432'
        ];

        foreach ($validCeps as $cep) {
            $this->assertTrue($this->validateCep($cep), "CEP {$cep} deveria ser válido");
        }
    }

    public function test_invalid_ceps(): void
    {
        $invalidCeps = [
            '01001-0000',
            '01001-00',
            '01001',
            'abcde-fgh',
            '12345-67a',
            '123456789',
            '1234567',
        ];

        foreach ($invalidCeps as $cep) {
            $this->assertFalse($this->validateCep($cep), "CEP {$cep} deveria ser inválido");
        }
    }

    public function test_empty_cep_is_valid(): void
    {
        $this->assertTrue($this->validateCep(''));
        $this->assertTrue($this->validateCep(null));
    }

    private function validateCep($cep): bool
    {
        $validator = $this->app['validator'];
        $data = ['cep' => $cep];
        $rules = ['cep' => [$this->cepRule]];
        
        $validator = $validator->make($data, $rules);
        
        return !$validator->fails();
    }
} 