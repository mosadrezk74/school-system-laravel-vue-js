<?php

namespace Modules\Setting\Services;

use Illuminate\Support\Facades\Cache;
use Modules\Setting\Models\SchoolSetting;

class SettingsService
{
    public const CACHE_KEY = 'school.settings';

    /**
     * The live model, fetched fresh on every call. Callers that need to
     * render the full record (e.g. an edit form) want an up-to-the-request
     * Eloquent instance, not a value pulled from cache.
     */
    public function get(): ?SchoolSetting
    {
        return SchoolSetting::query()->first();
    }

    public function isSetupCompleted(): bool
    {
        return (bool) ($this->snapshot()['setup_completed'] ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function bag(): array
    {
        return $this->snapshot()['settings'] ?? [];
    }

    public function value(string $key, mixed $default = null): mixed
    {
        return $this->bag()[$key] ?? $default;
    }

    /**
     * Cached as a plain array rather than the Eloquent model: a serialized
     * model can come back as __PHP_Incomplete_Class once the cache outlives
     * the request that wrote it, the same failure mode Language::active()
     * already works around.
     *
     * @return array<string, mixed>
     */
    private function snapshot(): array
    {
        return Cache::rememberForever(
            self::CACHE_KEY,
            fn (): array => SchoolSetting::query()->first()?->toArray() ?? [],
        );
    }

    public static function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
