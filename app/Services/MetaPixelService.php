<?php

namespace App\Services;

use App\Models\MetaPixel;
use App\Models\MetaPixelEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MetaPixelService
{
    public function create(array $data): MetaPixel
    {
        $pixel = MetaPixel::create($data);
        $this->flushFrontendCache();

        return $pixel;
    }

    public function update(MetaPixel $pixel, array $data): MetaPixel
    {
        $pixel->update($data);
        $this->flushFrontendCache();

        return $pixel->refresh();
    }

    public function delete(MetaPixel $pixel): void
    {
        $pixel->delete();
        $this->flushFrontendCache();
    }

    public function restore(MetaPixel $pixel): void
    {
        $pixel->restore();
        $this->flushFrontendCache();
    }

    public function forceDelete(MetaPixel $pixel): void
    {
        $pixel->forceDelete();
        $this->flushFrontendCache();
    }

    /**
     * Return live frontend pixel configurations as a Support Collection.
     *
     * Important: Laravel 13 can reject cached PHP objects when
     * cache.serializable_classes=false. Cache only plain arrays here so a
     * database/file/redis cache never hydrates an __PHP_Incomplete_Class.
     */
    public function activeForFrontend(): Collection
    {
        if (! config('meta-pixel.enabled', true)) {
            return collect();
        }

        $cacheKey = (string) config('meta-pixel.cache.key', 'meta_pixels.live.v2');
        $ttlSeconds = max(1, (int) config('meta-pixel.cache.ttl_seconds', 300));

        $cached = Cache::get($cacheKey);

        // Recover automatically from an old object-based/corrupted cache entry.
        if ($cached !== null && ! is_array($cached)) {
            Cache::forget($cacheKey);
            $cached = null;
        }

        if ($cached === null) {
            $cached = Cache::remember(
                $cacheKey,
                now()->addSeconds($ttlSeconds),
                fn (): array => MetaPixel::query()
                    ->live()
                    ->orderBy('id')
                    ->get([
                        'id',
                        'name',
                        'pixel_ids',
                        'full_script',
                        'lifecycle_status',
                        'track_page_view',
                        'track_ecommerce',
                        'starts_at',
                        'ends_at',
                    ])
                    ->map(static fn (MetaPixel $pixel): array => [
                        'id' => $pixel->id,
                        'name' => $pixel->name,
                        'pixel_ids' => array_values(array_filter(
                            (array) $pixel->pixel_ids,
                            static fn ($id): bool => filled($id)
                        )),
                        'full_script' => $pixel->full_script,
                        'lifecycle_status' => $pixel->lifecycle_status,
                        'track_page_view' => (bool) $pixel->track_page_view,
                        'track_ecommerce' => (bool) $pixel->track_ecommerce,
                        'starts_at' => $pixel->starts_at?->toIso8601String(),
                        'ends_at' => $pixel->ends_at?->toIso8601String(),
                    ])
                    ->all()
            );
        }

        return collect($cached)
            ->filter(static fn ($pixel): bool => is_array($pixel))
            ->map(static fn (array $pixel): object => (object) $pixel)
            ->values();
    }

    /** @return list<string> */
    public function activePixelIds(): array
    {
        return $this->activeForFrontend()
            ->pluck('pixel_ids')
            ->flatten()
            ->filter()
            ->map(static fn ($id): string => (string) $id)
            ->unique()
            ->values()
            ->all();
    }

    public function captureEvent(Request $request, array $data): MetaPixelEvent
    {
        return DB::transaction(function () use ($request, $data): MetaPixelEvent {
            $payload = $data['payload'] ?? [];

            return MetaPixelEvent::create([
                'user_id' => auth('web')->id(),
                'session_id' => $request->hasSession() ? $request->session()->getId() : null,
                'event_name' => $data['event_name'],
                'event_id' => $data['event_id'] ?? (string) Str::uuid(),
                'page_url' => $data['page_url'] ?? null,
                'referrer' => $data['referrer'] ?? null,
                'pixel_ids' => $this->activePixelIds(),
                'payload' => is_array($payload) ? $payload : [],
                'delivery_status' => $data['delivery_status'] ?? 'captured',
                'ip_hash' => $request->ip() ? hash('sha256', $request->ip().'|'.config('app.key')) : null,
                'user_agent' => Str::limit((string) $request->userAgent(), 1000, ''),
                'occurred_at' => now(),
            ]);
        });
    }

    public function flushFrontendCache(): void
    {
        // Forget both the current safe key and the previous object-cache key.
        foreach (array_unique([
            (string) config('meta-pixel.cache.key', 'meta_pixels.live.v2'),
            'meta_pixels.live',
        ]) as $cacheKey) {
            Cache::forget($cacheKey);
        }
    }
}
