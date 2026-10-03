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
        $pixel = MetaPixel::create($this->normalizeForPersistence($data));
        $this->flushFrontendCache();

        return $pixel;
    }

    public function update(MetaPixel $pixel, array $data): MetaPixel
    {
        $pixel->update($this->normalizeForPersistence($data));
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
     * Cache plain arrays only so strict Laravel cache serialization settings
     * never hydrate an incomplete Eloquent object.
     */
    public function activeForFrontend(): Collection
    {
        if (! config('meta-pixel.enabled', true)) {
            return collect();
        }

        $cacheKey = (string) config('meta-pixel.cache.key', 'meta_pixels.live.v2');
        $ttlSeconds = max(1, (int) config('meta-pixel.cache.ttl_seconds', 300));

        $cached = Cache::get($cacheKey);

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
                        'pixel_entries',
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
                        'pixel_entries' => $pixel->pixelEntries(),
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
        foreach (array_unique([
            (string) config('meta-pixel.cache.key', 'meta_pixels.live.v2'),
            'meta_pixels.live',
        ]) as $cacheKey) {
            Cache::forget($cacheKey);
        }
    }

    /** @return array<string, mixed> */
    private function normalizeForPersistence(array $data): array
    {
        $entries = collect($data['pixel_entries'] ?? [])
            ->filter(fn ($entry) => is_array($entry))
            ->map(fn (array $entry) => [
                'pixel_id' => trim((string) ($entry['pixel_id'] ?? '')),
                'script' => trim((string) ($entry['script'] ?? '')),
            ])
            ->filter(fn (array $entry) => $entry['pixel_id'] !== '')
            ->unique('pixel_id')
            ->values()
            ->all();

        $data['pixel_entries'] = $entries;
        $data['pixel_ids'] = collect($entries)->pluck('pixel_id')->values()->all();
        $data['full_script'] = collect($entries)
            ->pluck('script')
            ->first(fn ($script) => filled($script)) ?: null;

        return $data;
    }
}
