<?php

namespace App\Helpers;

class CpfHelper
{
    /**
     * validate CPF
     *
     * @param string $cpf
     * @return bool
     */
    public static function isValid(string $cpf): bool
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        if (strlen($cpf) !== 11) {
            return false;
        }
        
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }
        
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += (int) $cpf[$i] * (10 - $i);
        }
        $remainder = $sum % 11;
        $digit1 = $remainder < 2 ? 0 : 11 - $remainder;
        
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += (int) $cpf[$i] * (11 - $i);
        }
        $remainder = $sum % 11;
        $digit2 = $remainder < 2 ? 0 : 11 - $remainder;
        
        return (int) $cpf[9] === $digit1 && (int) $cpf[10] === $digit2;
    }
    
    /**
     * format CPF
     *
     * @param string $cpf
     * @return string
     */
    public static function format(string $cpf): string
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
    }
    
    /**
     * remove CPF format
     *
     * @param string $cpf
     * @return string
     */
    public static function unformat(string $cpf): string
    {
        return preg_replace('/[^0-9]/', '', $cpf);
    }
} 