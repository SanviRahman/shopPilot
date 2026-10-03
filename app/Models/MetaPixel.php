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
        'name', 'pixel_ids', 'pixel_entries', 'full_script', 'lifecycle_status', 'track_page_view',
        'track_ecommerce', 'starts_at', 'ends_at', 'settings',
    ];

    protected function casts(): array
    {
        return [
            'pixel_ids' => 'array',
            'pixel_entries' => 'array',
            'track_page_view' => 'boolean',
            'track_ecommerce' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'settings' => 'array',
        ];
    }

    /**
     * Return repeatable Pixel ID + script pairs.
     *
     * The legacy pixel_ids/full_script columns are still supported so an
     * existing database remains readable before/after the new migration.
     *
     * @return list<array{pixel_id:string, script:string}>
     */
    public function pixelEntries(): array
    {
        $entries = collect(is_array($this->pixel_entries) ? $this->pixel_entries : [])
            ->filter(fn ($entry) => is_array($entry))
            ->map(fn (array $entry) => [
                'pixel_id' => trim((string) ($entry['pixel_id'] ?? '')),
                'script' => (string) ($entry['script'] ?? ''),
            ])
            ->filter(fn (array $entry) => $entry['pixel_id'] !== '')
            ->values();

        if ($entries->isNotEmpty()) {
            return $entries->all();
        }

        return collect((array) $this->pixel_ids)
            ->filter()
            ->values()
            ->map(fn ($pixelId, $index) => [
                'pixel_id' => (string) $pixelId,
                'script' => $index === 0 ? (string) ($this->full_script ?? '') : '',
            ])
            ->all();
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
