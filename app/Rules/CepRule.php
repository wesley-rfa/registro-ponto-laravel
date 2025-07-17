<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CepRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return;
        }

        $cepLimpo = preg_replace('/[^0-9]/', '', $value);
        
        if (strlen($cepLimpo) !== 8) {
            $fail('O :attribute deve conter exatamente 8 dígitos numéricos.');
        }
    }
} 