<?php

namespace App\Services;

use App\Models\MetaPixel;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
                        'settings',
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
                        'settings' => (array) ($pixel->settings ?? []),
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

    /**
     * Dispatch event directly to Meta Conversions API (CAPI) without saving to database.
     *
     * @param Request $request
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function sendConversionApiEvent(Request $request, array $data): array
    {
        $activePixels = $this->activeForFrontend();
        if ($activePixels->isEmpty()) {
            return ['dispatched' => false, 'reason' => 'no_active_pixels'];
        }

        $capiConfig = (array) config('meta-pixel.capi', []);
        $globalCapiEnabled = (bool) ($capiConfig['enabled'] ?? true);
        $globalAccessToken = trim((string) ($capiConfig['access_token'] ?? ''));
        $apiVersion = trim((string) ($capiConfig['api_version'] ?? 'v21.0'));
        $globalTestEventCode = trim((string) ($capiConfig['test_event_code'] ?? ''));

        $eventName = (string) ($data['event_name'] ?? 'PageView');
        $eventId = (string) ($data['event_id'] ?? Str::uuid()->toString());
        $pageUrl = (string) ($data['page_url'] ?? $request->fullUrl());
        $payload = is_array($data['payload'] ?? null) ? $data['payload'] : [];

        // Build User Data according to Meta CAPI specification
        $userData = [
            'client_ip_address' => $request->ip(),
            'client_user_agent' => (string) $request->userAgent(),
        ];

        // Match with authenticated user or payload data if available
        $user = auth('web')->user();
        $email = $user?->email ?? ($payload['email'] ?? null);
        if ($email) {
            $userData['em'] = [hash('sha256', strtolower(trim((string) $email)))];
        }

        $phone = $user?->phone ?? ($payload['phone'] ?? null);
        if ($phone) {
            $normalizedPhone = preg_replace('/[^0-9]/', '', (string) $phone);
            if ($normalizedPhone) {
                $userData['ph'] = [hash('sha256', $normalizedPhone)];
            }
        }

        // Meta cookies for maximum event match quality
        if ($request->hasCookie('_fbp')) {
            $userData['fbp'] = $request->cookie('_fbp');
        }
        if ($request->hasCookie('_fbc')) {
            $userData['fbc'] = $request->cookie('_fbc');
        } elseif ($request->filled('fbclid')) {
            $userData['fbc'] = 'fb.1.' . time() . '.' . $request->query('fbclid');
        }

        // Custom data formatting
        $customData = [];
        if (isset($payload['value'])) {
            $customData['value'] = (float) $payload['value'];
        }
        if (isset($payload['currency'])) {
            $customData['currency'] = (string) $payload['currency'];
        }
        if (isset($payload['content_name'])) {
            $customData['content_name'] = (string) $payload['content_name'];
        }
        if (isset($payload['content_type'])) {
            $customData['content_type'] = (string) $payload['content_type'];
        }
        if (isset($payload['content_ids']) && is_array($payload['content_ids'])) {
            $customData['content_ids'] = array_map('strval', $payload['content_ids']);
        }
        if (isset($payload['contents']) && is_array($payload['contents'])) {
            $customData['contents'] = $payload['contents'];
        }
        if (isset($payload['search_string'])) {
            $customData['search_string'] = (string) $payload['search_string'];
        }

        $eventPayload = [
            'event_name' => $eventName,
            'event_time' => time(),
            'event_id' => $eventId,
            'event_source_url' => $pageUrl,
            'action_source' => 'website',
            'user_data' => $userData,
            'custom_data' => $customData,
        ];

        $results = [];
        $anyDispatched = false;

        foreach ($activePixels as $pixelConfig) {
            $pixelIds = (array) ($pixelConfig->pixel_ids ?? []);
            $pixelSettings = (array) ($pixelConfig->settings ?? []);

            $isCapiEnabled = array_key_exists('capi_enabled', $pixelSettings)
                ? (bool) $pixelSettings['capi_enabled']
                : $globalCapiEnabled;

            if (! $isCapiEnabled) {
                continue;
            }

            $token = filled($pixelSettings['capi_token'] ?? null)
                ? trim((string) $pixelSettings['capi_token'])
                : $globalAccessToken;

            if ($token === '') {
                continue;
            }

            $testCode = filled($pixelSettings['test_event_code'] ?? null)
                ? trim((string) $pixelSettings['test_event_code'])
                : $globalTestEventCode;

            $requestBody = ['data' => [$eventPayload]];
            if ($testCode !== '') {
                $requestBody['test_event_code'] = $testCode;
            }

            foreach ($pixelIds as $pixelId) {
                try {
                    $endpoint = "https://graph.facebook.com/{$apiVersion}/{$pixelId}/events";
                    $response = Http::timeout(3)
                        ->asJson()
                        ->post($endpoint . '?access_token=' . urlencode($token), $requestBody);

                    $results[$pixelId] = [
                        'status' => $response->status(),
                        'success' => $response->successful(),
                    ];
                    $anyDispatched = true;
                } catch (\Throwable $e) {
                    Log::warning('Meta Conversions API dispatch failed: ' . $e->getMessage(), [
                        'pixel_id' => $pixelId,
                        'event_name' => $eventName,
                        'event_id' => $eventId,
                    ]);

                    $results[$pixelId] = [
                        'status' => 500,
                        'success' => false,
                        'error' => $e->getMessage(),
                    ];
                }
            }
        }

        if (! $anyDispatched) {
            return [
                'dispatched' => false,
                'reason' => 'access_token_not_configured_or_capi_disabled',
                'browser_pixel_active' => true,
            ];
        }

        return [
            'dispatched' => true,
            'event_id' => $eventId,
            'event_name' => $eventName,
            'results' => $results,
        ];
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

        $settings = is_array($data['settings'] ?? null) ? $data['settings'] : [];
        if (array_key_exists('capi_enabled', $data) || array_key_exists('capi_token', $data) || array_key_exists('test_event_code', $data)) {
            $settings['capi_enabled'] = ! empty($data['capi_enabled']);
            $settings['capi_token'] = trim((string) ($data['capi_token'] ?? ''));
            $settings['test_event_code'] = trim((string) ($data['test_event_code'] ?? ''));
        }
        $data['settings'] = $settings;

        return $data;
    }
}
