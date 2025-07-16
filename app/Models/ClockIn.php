<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClockIn extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'registered_at',
    ];

    protected function casts(): array
    {
        return [
            'registered_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('registered_at', [$startDate, $endDate]);
    }
}
