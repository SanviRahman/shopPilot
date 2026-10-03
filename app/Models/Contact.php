<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUSES = ['active', 'inactive'];

    protected $fillable = [
        'name',
        'email',
        'phone',
        'map_url',
        'status',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
