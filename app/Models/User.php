<?php

namespace App\Models;

use App\Enums\UserRoleEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'created_by',
        'name',
        'email',
        'cpf',
        'password',
        'job_title',
        'birth_date',
        'postal_code',
        'address',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'password' => 'hashed',
            'role' => UserRoleEnum::class,
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function createdUsers()
    {
        return $this->hasMany(User::class, 'created_by');
    }

    public function clockIns()
    {
        return $this->hasMany(ClockIn::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRoleEnum::ADMIN;
    }

    public function isEmployee(): bool
    {
        return $this->role === UserRoleEnum::EMPLOYEE;
    }

    public static function getRoles(): array
    {
        return [
            UserRoleEnum::ADMIN->value,
            UserRoleEnum::EMPLOYEE->value,
        ];
    }
}
