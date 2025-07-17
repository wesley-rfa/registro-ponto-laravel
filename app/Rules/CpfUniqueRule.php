<?php

namespace App\Rules;

use App\Helpers\CpfHelper;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\App;

class CpfUniqueRule implements ValidationRule
{
    private ?int $ignoreUserId;

    public function __construct(?int $ignoreUserId = null)
    {
        $this->ignoreUserId = $ignoreUserId;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $cleanCpf = CpfHelper::unformat($value);
        /** @var UserRepositoryInterface $userRepository */
        $userRepository = App::make(UserRepositoryInterface::class);
        $exists = $userRepository->existsByCpf($cleanCpf, $this->ignoreUserId);
        if ($exists) {
            $fail('Este CPF já está em uso.');
        }
    }
} 