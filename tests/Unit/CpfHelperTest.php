<?php

namespace Tests\Unit;

use App\Helpers\CpfHelper;
use Tests\TestCase;

class CpfHelperTest extends TestCase
{
    /**
     * test valid CPFs
     */
    public function test_valid_cpfs(): void
    {
        $validCpfs = [
            '529.982.247-25',
            '111.444.777-35',
            '123.456.789-09',
            '52998224725',
            '11144477735',
            '12345678909'
        ];

        foreach ($validCpfs as $cpf) {
            $this->assertTrue(CpfHelper::isValid($cpf), "CPF {$cpf} deveria ser válido");
        }
    }

    /**
     * test invalid CPFs
     */
    public function test_invalid_cpfs(): void
    {
        $invalidCpfs = [
            '111.111.111-11',
            '222.222.222-22',
            '123.456.789-10',
            '000.000.000-00',
            '11111111111',
            '22222222222',
            '12345678910',
            '00000000000',
            '123.456.789-0',
            '123.456.789-123',
            'abc.def.ghi-jk',
            '1234567890',
            '123456789012'
        ];

        foreach ($invalidCpfs as $cpf) {
            $this->assertFalse(CpfHelper::isValid($cpf), "CPF {$cpf} deveria ser inválido");
        }
    }

    /**
     * test format CPF
     */
    public function test_format_cpf(): void
    {
        $this->assertEquals('529.982.247-25', CpfHelper::format('52998224725'));
        $this->assertEquals('529.982.247-25', CpfHelper::format('529.982.247-25'));
        $this->assertEquals('111.444.777-35', CpfHelper::format('11144477735'));
    }

    /**
     * test unformat CPF
     */
    public function test_unformat_cpf(): void
    {
        $this->assertEquals('52998224725', CpfHelper::unformat('529.982.247-25'));
        $this->assertEquals('52998224725', CpfHelper::unformat('52998224725'));
        $this->assertEquals('11144477735', CpfHelper::unformat('111.444.777-35'));
    }
} 