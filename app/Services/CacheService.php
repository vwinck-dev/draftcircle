<?php

declare(strict_types=1);

namespace App\Services;

use Closure;
use Illuminate\Support\Facades\Cache;

final readonly class CacheService
{
    /**
     * Cache callbacks intentionally return mixed because Laravel's cache repository stores arbitrary serializable values.
     *
     * @template T
     *
     * @param Closure(): T $callback
     * @return T
     */
    public function remember(string $key, int $seconds, Closure $callback): mixed
    {
        return Cache::remember(key: $key, ttl: $seconds, callback: $callback);
    }

    public function flushBooks(): void
    {
        $this->flushTags(tags: ['books']);
        Cache::forget('categories:all');
        Cache::forget('home:catalog');
    }

    public function flushLoans(): void
    {
        $this->flushTags(tags: ['loans']);
        Cache::forget('admin:summary');
    }

    public function flushSettings(): void
    {
        Cache::forget('settings:all');
    }

    /** @param list<string> $tags */
    private function flushTags(array $tags): void
    {
        $store = Cache::store();

        if ($store->supportsTags()) {
            $store->tags($tags)->flush();
        }
    }
}
