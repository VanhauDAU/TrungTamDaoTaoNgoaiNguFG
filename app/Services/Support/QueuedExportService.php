<?php

namespace App\Services\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class QueuedExportService
{
    public function get(string $namespace, array $context): ?array
    {
        $key = $this->cacheKey($namespace, $context);

        try {
            $payload = Cache::store($this->store())->get($key);
        } catch (Throwable) {
            $payload = Cache::get($key);
        }

        return is_array($payload) ? $payload : null;
    }

    public function markQueued(string $namespace, array $context, array $payload): void
    {
        $this->put($namespace, $context, [
            'status' => 'queued',
            'path' => null,
            'filename' => null,
            'mime' => null,
            'queued_at' => now()->toIso8601String(),
            'message' => null,
            ...$payload,
        ]);
    }

    public function markReady(string $namespace, array $context, array $payload): void
    {
        $this->put($namespace, $context, [
            'status' => 'ready',
            'queued_at' => null,
            'ready_at' => now()->toIso8601String(),
            'message' => null,
            ...$payload,
        ]);
    }

    public function markFailed(string $namespace, array $context, string $message): void
    {
        $this->put($namespace, $context, [
            'status' => 'failed',
            'path' => null,
            'filename' => null,
            'mime' => null,
            'queued_at' => null,
            'message' => $message,
        ]);
    }

    public function downloadIfReady(string $namespace, array $context): ?BinaryFileResponse
    {
        $payload = $this->get($namespace, $context);

        if (!is_array($payload) || ($payload['status'] ?? null) !== 'ready') {
            return null;
        }

        $path = (string) ($payload['path'] ?? '');

        if ($path === '' || !Storage::disk($this->disk())->exists($path)) {
            $this->markFailed($namespace, $context, 'File export không còn tồn tại trên hệ thống.');
            return null;
        }

        return response()->download(
            Storage::disk($this->disk())->path($path),
            (string) ($payload['filename'] ?? basename($path)),
            [
                'Content-Type' => (string) ($payload['mime'] ?? 'application/octet-stream'),
            ]
        );
    }

    public function buildStoragePath(string $namespace, string $filename): string
    {
        $datePath = now()->format('Y/m/d');
        $safeName = Str::slug(pathinfo($filename, PATHINFO_FILENAME), '-');
        $extension = strtolower((string) pathinfo($filename, PATHINFO_EXTENSION));
        $suffix = Str::lower((string) Str::ulid());

        return trim($namespace, '/')
            . '/' . $datePath
            . '/' . ($safeName !== '' ? $safeName : 'export')
            . '-' . $suffix
            . ($extension !== '' ? '.' . $extension : '');
    }

    private function put(string $namespace, array $context, array $payload): void
    {
        $key = $this->cacheKey($namespace, $context);
        $ttl = now()->addMinutes($this->ttlMinutes());

        try {
            Cache::store($this->store())->put($key, $payload, $ttl);
            return;
        } catch (Throwable) {
            Cache::put($key, $payload, $ttl);
        }
    }

    private function cacheKey(string $namespace, array $context): string
    {
        return sprintf(
            'queued-export:%s:%s',
            trim($namespace, ':'),
            sha1((string) json_encode($this->normalize($context), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
        );
    }

    private function normalize(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }

        if ($value !== [] && array_keys($value) !== range(0, count($value) - 1)) {
            ksort($value);
        }

        foreach ($value as $key => $item) {
            $value[$key] = $this->normalize($item);
        }

        return $value;
    }

    private function store(): string
    {
        return (string) config('cache.queued_exports.store', 'redis');
    }

    private function ttlMinutes(): int
    {
        return max(5, (int) config('cache.queued_exports.ttl', 30));
    }

    private function disk(): string
    {
        return (string) config('cache.queued_exports.disk', 'local');
    }
}
