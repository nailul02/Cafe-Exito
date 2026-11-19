<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\Station;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $fillable = ['name', 'username', 'email', 'password', 'role', 'station', 'is_active', 'last_login_at'];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'role' => UserRole::class,
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function extraPermissions(): HasMany
    {
        return $this->hasMany(UserPermission::class);
    }

    /** Legacy role fallback keeps existing rows usable until the role/station migration is applied. */
    public function getStationAttribute($value): ?Station
    {
        if (is_string($value) && $value !== '') {
            return Station::tryFrom($value);
        }

        return match ($this->attributes['role'] ?? null) {
            'barista' => Station::Barista,
            'kitchen' => Station::Kitchen,
            default => null,
        };
    }
}
