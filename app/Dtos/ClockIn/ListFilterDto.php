<?php

namespace App\Dtos\ClockIn;

use Carbon\Carbon;

class ListFilterDto
{
    public function __construct(
        public readonly ?Carbon $startDate,
        public readonly ?Carbon $endDate,
    ) {}

    public static function create(array $request): self
    {
        return new self(
            startDate: isset($request['start_date']) ? Carbon::parse($request['start_date']) : null,
            endDate: isset($request['end_date']) ? Carbon::parse($request['end_date']) : null,
        );
    }
}