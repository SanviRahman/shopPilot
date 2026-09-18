<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isStaff(): bool
    {
        return $this->hasAnyRole(['Admin', 'Manager', 'Agent']);
    }

    public function scopeStaff(Builder $query): Builder
    {
        return $query->role(['Admin', 'Manager', 'Agent']);
    }

    public function scopeCustomers(Builder $query): Builder
    {
        return $query->role('Customer');
    }
}