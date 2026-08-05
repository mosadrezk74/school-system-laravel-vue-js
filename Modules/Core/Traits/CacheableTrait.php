<?php

namespace Modules\Core\Traits;

use Illuminate\Support\Facades\Cache;

trait CacheableTrait
{
    protected function cacheRemember(string $key, mixed $data, int $ttl = 3600): mixed
    {
        $key = $this->getCacheKey($key);
        return Cache::remember($key, $ttl, function () use ($data) {
            return is_callable($data) ? $data() : $data;
        });
    }

    protected function cacheRememberForever(string $key, mixed $data): mixed
    {
        $key = $this->getCacheKey($key);
        return Cache::rememberForever($key, function () use ($data) {
            return is_callable($data) ? $data() : $data;
        });
    }

    protected function cacheGet(string $key): mixed
    {
        return Cache::get($this->getCacheKey($key));
    }

    protected function cachePut(string $key, mixed $data, int $ttl = 3600): bool
    {
        return Cache::put($this->getCacheKey($key), $data, $ttl);
    }

    protected function cacheForget(string $key): bool
    {
        return Cache::forget($this->getCacheKey($key));
    }

    protected function cacheHas(string $key): bool
    {
        return Cache::has($this->getCacheKey($key));
    }

    protected function cacheFlush(): bool
    {
        $keys = $this->getCacheKeys();
        $result = true;

        foreach ($keys as $key) {
            if (!Cache::forget($key)) {
                $result = false;
            }
        }

        return $result;
    }

    protected function getCacheKey(string $key): string
    {
        $prefix = $this->getCachePrefix();
        return "{$prefix}_{$key}";
    }

    protected function getCachePrefix(): string
    {
        if (property_exists($this, 'cachePrefix')) {
            return $this->cachePrefix;
        }

        return class_basename($this);
    }

    protected function getCacheKeys(): array
    {
        if (property_exists($this, 'cacheKeys')) {
            return $this->cacheKeys;
        }

        return [];
    }

    protected function cacheTags(array $tags, string $key, mixed $data, int $ttl = 3600): mixed
    {
        if (method_exists(Cache::getStore(), 'tags')) {
            return Cache::tags($tags)->remember($key, $ttl, function () use ($data) {
                return is_callable($data) ? $data() : $data;
            });
        }

        return $this->cacheRemember($key, $data, $ttl);
    }

    protected function cacheTagsForget(array $tags): bool
    {
        if (method_exists(Cache::getStore(), 'tags')) {
            Cache::tags($tags)->flush();
            return true;
        }

        return false;
    }
}
