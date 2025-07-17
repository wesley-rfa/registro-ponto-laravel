<?php

namespace App\Rules;

use App\Helpers\CpfHelper;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class CpfUniqueRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $cleanCpf = CpfHelper::unformat($value);
        
        $exists = User::where('cpf', $cleanCpf)->exists();
            
        if ($exists) {
            $fail('Este CPF já está em uso.');
        }
    }
} 