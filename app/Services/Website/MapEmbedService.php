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
        $mapUrl = $this->extractUrl(trim((string) $mapUrl));

        if ($mapUrl === null) {
            return null;
        }

        $direct = $this->buildEmbedUrl($mapUrl);

        if ($direct !== null) {
            return $direct;
        }

        if (! $this->isGoogleShortUrl($mapUrl)) {
            return null;
        }

        $resolved = $this->resolveShortUrl($mapUrl);

        return $resolved ? $this->buildEmbedUrl($resolved) : null;
    }

    public function extractUrl(string $value): ?string
    {
        $value = trim(html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        if ($value === '') {
            return null;
        }

        if (preg_match('~<iframe\b[^>]*\bsrc\s*=\s*(["\'])(.*?)\1~is', $value, $match)) {
            $value = trim(html_entity_decode((string) $match[2], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }

        return filter_var($value, FILTER_VALIDATE_URL) ? $value : null;
    }

    private function buildEmbedUrl(string $url): ?string
    {
        $parts = parse_url($url);
        $host = Str::lower((string) ($parts['host'] ?? ''));
        $path = (string) ($parts['path'] ?? '');
        $query = (string) ($parts['query'] ?? '');
        $fragment = (string) ($parts['fragment'] ?? '');
        parse_str($query, $params);

        if ($host === '' || ! $this->isKnownMapHost($host)) {
            return null;
        }

        if ($this->isGoogleHost($host) && (str_contains($path, '/maps/embed') || (($params['output'] ?? null) === 'embed'))) {
            return $url;
        }

        if ($host === 'www.openstreetmap.org' && str_contains($path, '/export/embed.html')) {
            return $url;
        }

        $coordinates = $this->coordinatesFromUrl($url);

        if ($coordinates !== null) {
            return 'https://www.google.com/maps?q='.rawurlencode($coordinates).'&z=16&output=embed';
        }

        foreach (['q', 'query', 'destination', 'll'] as $key) {
            $value = trim((string) ($params[$key] ?? ''));

            if ($value !== '') {
                return 'https://www.google.com/maps?q='.rawurlencode($value).'&output=embed';
            }
        }

        if (preg_match('~/place/([^/]+)~i', $path, $match)) {
            $place = trim(str_replace('+', ' ', rawurldecode($match[1])));

            if ($place !== '') {
                return 'https://www.google.com/maps?q='.rawurlencode($place).'&output=embed';
            }
        }

        if ($host === 'www.openstreetmap.org' && preg_match('~map=\d+/(-?\d+(?:\.\d+)?)/(-?\d+(?:\.\d+)?)~', $fragment, $match)) {
            return 'https://www.google.com/maps?q='.rawurlencode($match[1].','.$match[2]).'&z=16&output=embed';
        }

        return null;
    }

    private function coordinatesFromUrl(string $url): ?string
    {
        if (preg_match('~@(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)~', $url, $match)) {
            return $match[1].','.$match[2];
        }

        if (preg_match('~!3d(-?\d+(?:\.\d+)?).*?!4d(-?\d+(?:\.\d+)?)~', $url, $match)) {
            return $match[1].','.$match[2];
        }

        return null;
    }

    private function resolveShortUrl(string $url): ?string
    {
        $cacheKey = 'shoppilot.map.resolved.'.sha1($url);

        return Cache::remember($cacheKey, now()->addDay(), function () use ($url): ?string {
            $resolved = $this->resolveWithLaravelHttp($url);

            if ($resolved !== null) {
                return $resolved;
            }

            return $this->resolveWithNativeSocket($url);
        });
    }

    private function resolveWithLaravelHttp(string $url): ?string
    {
        if (! $this->httpTransportAvailable()) {
            return null;
        }

        try {
            $response = Http::connectTimeout(3)->timeout(7)->withHeaders(['User-Agent' => 'Mozilla/5.0 ShopPilot/1.0'])->withOptions(['allow_redirects' => ['max' => 6, 'strict' => false, 'referer' => true, 'track_redirects' => true]])->get($url);
            $finalUrl = trim((string) ($response->handlerStats()['url'] ?? ''));

            return $finalUrl !== '' && filter_var($finalUrl, FILTER_VALIDATE_URL) && $this->isSafeGoogleRedirectUrl($finalUrl) ? $finalUrl : null;
        } catch (Throwable) {
            return null;
        }
    }

    private function resolveWithNativeSocket(string $url): ?string
    {
        if (! function_exists('stream_socket_client') || ! extension_loaded('openssl')) {
            return null;
        }

        $current = $url;

        for ($redirect = 0; $redirect < 6; $redirect++) {
            if (! $this->isSafeGoogleRedirectUrl($current)) {
                return null;
            }

            $parts = parse_url($current);
            $host = Str::lower((string) ($parts['host'] ?? ''));
            $scheme = Str::lower((string) ($parts['scheme'] ?? 'https'));

            if ($host === '' || $scheme !== 'https') {
                return null;
            }

            $path = (string) ($parts['path'] ?? '/');
            $path = $path !== '' ? $path : '/';

            if (! empty($parts['query'])) {
                $path .= '?'.$parts['query'];
            }

            $context = stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true, 'peer_name' => $host, 'SNI_enabled' => true]]);
            $errno = 0;
            $error = '';
            $socket = @stream_socket_client('ssl://'.$host.':443', $errno, $error, 5, STREAM_CLIENT_CONNECT, $context);

            if (! is_resource($socket)) {
                return null;
            }

            stream_set_timeout($socket, 7);
            $request = "GET {$path} HTTP/1.1\r\nHost: {$host}\r\nUser-Agent: Mozilla/5.0 ShopPilot/1.0\r\nAccept: text/html,application/xhtml+xml\r\nConnection: close\r\n\r\n";
            fwrite($socket, $request);
            $response = stream_get_contents($socket, 524288);
            fclose($socket);

            if (! is_string($response) || $response === '') {
                return null;
            }

            [$headerText, $body] = array_pad(preg_split("/\r?\n\r?\n/", $response, 2), 2, '');
            $status = preg_match('~^HTTP/\S+\s+(\d{3})~i', $headerText, $statusMatch) ? (int) $statusMatch[1] : 0;

            if ($status >= 300 && $status < 400 && preg_match('~^Location:\s*(.+)$~im', $headerText, $locationMatch)) {
                $next = $this->resolveRelativeUrl($current, trim($locationMatch[1]));

                if ($next === null) {
                    return null;
                }

                $current = $next;
                continue;
            }

            if ($status >= 200 && $status < 300) {
                $bodyUrl = $this->extractGoogleMapUrlFromHtml($body);

                return $bodyUrl ?: $current;
            }

            return null;
        }

        return $this->isSafeGoogleRedirectUrl($current) ? $current : null;
    }

    private function extractGoogleMapUrlFromHtml(string $html): ?string
    {
        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $patterns = [
            '~https://(?:www\.)?google\.[^"\'<>\s]+/maps[^"\'<>\s]+~i',
            '~https://maps\.google\.[^"\'<>\s]+/[^"\'<>\s]+~i',
            '~url=([^"\'<>\s]+google[^"\'<>\s]+maps[^"\'<>\s]+)~i',
        ];

        foreach ($patterns as $pattern) {
            if (! preg_match($pattern, $html, $match)) {
                continue;
            }

            $candidate = rawurldecode((string) ($match[1] ?? $match[0]));
            $candidate = str_replace(['\\u003d', '\\u0026', '\\/'], ['=', '&', '/'], $candidate);
            $candidate = trim($candidate, " \t\n\r\0\x0B\"'");

            if (filter_var($candidate, FILTER_VALIDATE_URL) && $this->isSafeGoogleRedirectUrl($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function resolveRelativeUrl(string $base, string $location): ?string
    {
        if (filter_var($location, FILTER_VALIDATE_URL)) {
            return $location;
        }

        $parts = parse_url($base);
        $scheme = (string) ($parts['scheme'] ?? 'https');
        $host = (string) ($parts['host'] ?? '');

        if ($host === '') {
            return null;
        }

        if (str_starts_with($location, '//')) {
            return $scheme.':'.$location;
        }

        if (str_starts_with($location, '/')) {
            return $scheme.'://'.$host.$location;
        }

        $basePath = (string) ($parts['path'] ?? '/');
        $directory = rtrim(str_replace('\\', '/', dirname($basePath)), '/');

        return $scheme.'://'.$host.($directory !== '' ? $directory : '').'/'.$location;
    }

    private function httpTransportAvailable(): bool
    {
        if (extension_loaded('curl') && function_exists('curl_init')) {
            return true;
        }

        $allowUrlFopen = strtolower(trim((string) ini_get('allow_url_fopen')));

        return in_array($allowUrlFopen, ['1', 'on', 'true', 'yes'], true);
    }

    private function isGoogleShortUrl(string $url): bool
    {
        $host = Str::lower((string) parse_url($url, PHP_URL_HOST));

        return in_array($host, ['maps.app.goo.gl', 'goo.gl'], true);
    }

    private function isSafeGoogleRedirectUrl(string $url): bool
    {
        $parts = parse_url($url);
        $scheme = Str::lower((string) ($parts['scheme'] ?? ''));
        $host = Str::lower((string) ($parts['host'] ?? ''));

        return $scheme === 'https' && $this->isGoogleHost($host);
    }

    private function isGoogleHost(string $host): bool
    {
        return $host === 'goo.gl' || $host === 'maps.app.goo.gl' || $host === 'google.com' || $host === 'www.google.com' || str_ends_with($host, '.google.com') || preg_match('~(^|\.)google\.[a-z.]{2,}$~i', $host) === 1;
    }

    private function isKnownMapHost(string $host): bool
    {
        return $this->isGoogleHost($host) || $host === 'maps.apple.com' || $host === 'www.openstreetmap.org' || $host === 'openstreetmap.org' || str_contains($host, 'googleusercontent.com');
    }
}
