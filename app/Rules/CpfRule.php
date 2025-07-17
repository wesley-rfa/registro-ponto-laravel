<?php

namespace App\Rules;

use App\Helpers\CpfHelper;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CpfRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!CpfHelper::isValid($value)) {
            $fail('O :attribute informado não é válido.');
        }
    }
} 