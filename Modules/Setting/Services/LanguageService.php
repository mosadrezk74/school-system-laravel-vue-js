<?php

namespace Modules\Setting\Services;

use Illuminate\Support\Collection;
use Modules\Setting\Models\Language;

class LanguageService
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function active(): Collection
    {
        return Language::active();
    }

    /**
     * @return array<int, string>
     */
    public function activeLocales(): array
    {
        return Language::activeLocales();
    }

    public function defaultLocale(): string
    {
        return Language::defaultLocale();
    }

    public function isSupported(?string $locale): bool
    {
        return Language::isSupported($locale);
    }

    public function directionFor(?string $locale): string
    {
        return Language::directionFor($locale);
    }

    /**
     * @return Collection<int, Language>
     */
    public function all(): Collection
    {
        return Language::query()
            ->orderByDesc('is_default')
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();
    }
}
