<?php

namespace App\Services\Website;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class MapEmbedService
{
    public function embedUrl(?string $mapUrl): ?string
    {
        $mapUrl = trim((string) $mapUrl);
        if ($mapUrl === '' || ! filter_var($mapUrl, FILTER_VALIDATE_URL)) return null;

        $direct = $this->buildEmbedUrl($mapUrl);
        if ($direct !== null) return $direct;
        if (! $this->isGoogleShortUrl($mapUrl)) return null;

        $resolved = $this->resolveShortUrl($mapUrl);
        return $resolved ? $this->buildEmbedUrl($resolved) : null;
    }

    private function buildEmbedUrl(string $url): ?string
    {
        $parts = parse_url($url);
        $host = Str::lower((string) ($parts['host'] ?? ''));
        $path = (string) ($parts['path'] ?? '');
        $query = (string) ($parts['query'] ?? '');
        $fragment = (string) ($parts['fragment'] ?? '');
        parse_str($query, $params);

        if ($host === '' || ! $this->isKnownMapHost($host)) return null;
        if (str_contains($host, 'google.') && (str_contains($path, '/maps/embed') || (($params['output'] ?? null) === 'embed'))) return $url;
        if ($host === 'www.openstreetmap.org' && str_contains($path, '/export/embed.html')) return $url;

        $coordinates = $this->coordinatesFromUrl($url);
        if ($coordinates !== null) return 'https://www.google.com/maps?q='.rawurlencode($coordinates).'&z=16&output=embed';

        foreach (['q', 'query', 'destination', 'll'] as $key) {
            $value = trim((string) ($params[$key] ?? ''));
            if ($value !== '') return 'https://www.google.com/maps?q='.rawurlencode($value).'&output=embed';
        }

        if (preg_match('~/place/([^/]+)~i', $path, $match)) {
            $place = trim(str_replace('+', ' ', rawurldecode($match[1])));
            if ($place !== '') return 'https://www.google.com/maps?q='.rawurlencode($place).'&output=embed';
        }

        if ($host === 'www.openstreetmap.org' && preg_match('~map=\d+/(-?\d+(?:\.\d+)?)/(-?\d+(?:\.\d+)?)~', $fragment, $match)) return 'https://www.google.com/maps?q='.rawurlencode($match[1].','.$match[2]).'&z=16&output=embed';

        return null;
    }

    private function coordinatesFromUrl(string $url): ?string
    {
        if (preg_match('~@(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)~', $url, $match)) return $match[1].','.$match[2];
        if (preg_match('~!3d(-?\d+(?:\.\d+)?).*?!4d(-?\d+(?:\.\d+)?)~', $url, $match)) return $match[1].','.$match[2];
        return null;
    }

    private function resolveShortUrl(string $url): ?string
    {
        $cacheKey = 'shoppilot.map.resolved.'.sha1($url);
        return Cache::remember($cacheKey, now()->addDay(), function () use ($url): ?string {
            try {
                $response = Http::connectTimeout(3)->timeout(7)->withHeaders(['User-Agent' => 'Mozilla/5.0 ShopPilot/1.0'])->withOptions(['allow_redirects' => ['max' => 6, 'strict' => false, 'referer' => true, 'track_redirects' => true]])->get($url);
                $finalUrl = trim((string) ($response->handlerStats()['url'] ?? ''));
                return $finalUrl !== '' && filter_var($finalUrl, FILTER_VALIDATE_URL) ? $finalUrl : null;
            } catch (Throwable $exception) {
                report($exception);
                return null;
            }
        });
    }

    private function isGoogleShortUrl(string $url): bool
    {
        $host = Str::lower((string) parse_url($url, PHP_URL_HOST));
        return in_array($host, ['maps.app.goo.gl', 'goo.gl'], true);
    }

    private function isKnownMapHost(string $host): bool
    {
        return $host === 'maps.app.goo.gl' || $host === 'goo.gl' || $host === 'maps.apple.com' || $host === 'www.openstreetmap.org' || $host === 'openstreetmap.org' || str_contains($host, 'google.com') || str_contains($host, 'google.') || str_contains($host, 'googleusercontent.com');
    }
}
