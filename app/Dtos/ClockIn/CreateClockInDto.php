<?php

namespace App\Dtos\ClockIn;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CreateClockInDto
{
    public function __construct(
        public readonly int $user_id,
        public readonly Carbon $registered_at,
    ) {}
    
    public static function create(): self
    {
        return new self(
            user_id: Auth::id(),
            registered_at: Carbon::now(),
        );
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->user_id,
            'registered_at' => $this->registered_at,
        ];
    }
}