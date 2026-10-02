<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MetaPixel extends Model
{
    use HasFactory, SoftDeletes;

    public const LIFECYCLE = ['draft', 'testing', 'active', 'paused', 'archived'];

    protected $fillable = [
        'name', 'pixel_ids', 'full_script', 'lifecycle_status', 'track_page_view',
        'track_ecommerce', 'starts_at', 'ends_at', 'settings',
    ];

    protected function casts(): array
    {
        return [
            'pixel_ids' => 'array',
            'track_page_view' => 'boolean',
            'track_ecommerce' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'settings' => 'array',
        ];
    }

    public function scopeLive(Builder $query): Builder
    {
        $now = now();
        $liveState = (string) config('meta-pixel.lifecycle.live_state', 'active');

        return $query->where('lifecycle_status', $liveState)
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now));
    }
}
