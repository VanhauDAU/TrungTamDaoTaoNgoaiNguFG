<?php

namespace App\Services\Client;

use Closure;
use Illuminate\Support\Facades\Cache;
use Throwable;

class PublicContentCacheService
{
    public function remember(string $namespace, array $context, Closure $callback, ?int $ttlSeconds = null): mixed
    {
        $cacheKey = $this->buildKey($namespace, $context);
        $ttl = now()->addSeconds($ttlSeconds ?? $this->defaultTtl());

        try {
            return Cache::store($this->store())->remember($cacheKey, $ttl, $callback);
        } catch (Throwable) {
            return $callback();
        }
    }

    public function bust(): void
    {
        $version = (string) now()->format('Uu');

        try {
            Cache::store($this->store())->forever($this->versionKey(), $version);
            return;
        } catch (Throwable) {
            Cache::forever($this->versionKey(), $version);
        }
    }

    private function buildKey(string $namespace, array $context): string
    {
        $payload = json_encode(
            $this->normalize($context),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        return sprintf(
            'public-content:%s:%s:%s',
            $this->version(),
            trim($namespace, ':'),
            sha1((string) $payload)
        );
    }

    private function version(): string
    {
        try {
            return (string) Cache::store($this->store())->get($this->versionKey(), '1');
        } catch (Throwable) {
            return (string) Cache::get($this->versionKey(), '1');
        }
    }

    private function normalize(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }

        if ($this->isAssoc($value)) {
            ksort($value);
        }

        foreach ($value as $key => $item) {
            $value[$key] = $this->normalize($item);
        }

        return $value;
    }

    private function isAssoc(array $value): bool
    {
        if ($value === []) {
            return false;
        }

        return array_keys($value) !== range(0, count($value) - 1);
    }

    private function store(): string
    {
        return (string) config('cache.public_lists.store', 'redis');
    }

    private function defaultTtl(): int
    {
        return (int) config('cache.public_lists.ttl', 300);
    }

    private function versionKey(): string
    {
        return (string) config('cache.public_lists.version_key', 'public-content:version');
    }
}
