<?php

namespace Modules\Setting\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * The single source of truth for which languages this school runs in.
 *
 * Adding a language is one row — no code change, no deployment. Everything
 * downstream (the navbar switcher, RTL direction, translatable form fields,
 * validation of an incoming locale) reads from here.
 */
class Language extends Model
{
    public const CACHE_KEY = 'languages.active';

    protected $table = 'languages';

    protected $guarded = ['id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        // Any write clears the cache, so enabling a language takes effect
        // immediately instead of after a TTL.
        static::saved(fn () => static::forgetCache());
        static::deleted(fn () => static::forgetCache());
    }

    public static function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Languages the school switched on, default first.
     *
     * Cached as plain arrays rather than models: a serialized Eloquent
     * collection comes back as __PHP_Incomplete_Class once the cache outlives
     * the request that wrote it.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public static function active(): Collection
    {
        $rows = Cache::rememberForever(self::CACHE_KEY, fn (): array => static::query()
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'locale', 'direction', 'is_default', 'is_active'])
            ->map(fn (Language $l): array => [
                'id' => $l->id,
                'name' => $l->name,
                'code' => $l->code,
                'locale' => $l->locale,
                'direction' => $l->direction,
                'is_default' => (bool) $l->is_default,
            ])
            ->all());

        return collect($rows);
    }

    /**
     * @return array<int, string>
     */
    public static function activeLocales(): array
    {
        return static::active()->pluck('locale')->all();
    }

    public static function defaultLocale(): string
    {
        $active = static::active();

        return $active->firstWhere('is_default', true)['locale']
            ?? $active->first()['locale']
            ?? config('app.fallback_locale', 'en');
    }

    public static function isSupported(?string $locale): bool
    {
        return $locale !== null && in_array($locale, static::activeLocales(), true);
    }

    public static function directionFor(?string $locale): string
    {
        return static::active()->firstWhere('locale', $locale)['direction'] ?? 'ltr';
    }

    public function isRtl(): bool
    {
        return $this->direction === 'rtl';
    }

    /**
     * @param  Builder<Language>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
