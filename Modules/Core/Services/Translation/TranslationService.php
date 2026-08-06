<?php

namespace Modules\Core\Services\Translation;

use Illuminate\Support\Facades\Log;
use Modules\Core\Contracts\TranslationProviderInterface;
use Modules\Setting\Services\LanguageService;
use Modules\Setting\Services\SettingsService;

class TranslationService
{
    public function __construct(
        private readonly TranslationProviderInterface $provider,
        private readonly LanguageService $languages,
        private readonly SettingsService $settings,
    ) {}

    /**
     * The locale the user actually typed in: the one non-empty value among
     * the submitted translations, falling back to the app locale when that's
     * ambiguous (e.g. editing a record that already has more than one).
     *
     * @param  array<string, string|null>  $translations
     */
    public function detectSourceLocale(array $translations): string
    {
        return array_key_first(array_filter($translations)) ?? app()->getLocale();
    }

    /**
     * @param  array<string, string|null>  $translations
     * @return array<string, string|null>
     */
    public function fillMissing(array $translations, string $sourceLocale, bool $force = false): array
    {
        if (! $this->settings->value('ai_translation_enabled', true)) {
            return $translations;
        }

        $sourceText = $translations[$sourceLocale] ?? null;

        if (! $sourceText) {
            return $translations;
        }

        foreach ($this->languages->activeLocales() as $locale) {
            if ($locale === $sourceLocale) {
                continue;
            }

            if (! $force && ! empty($translations[$locale])) {
                continue;
            }

            try {
                $translated = $this->provider->translate($sourceText, $sourceLocale, $locale);
            } catch (\Throwable $exception) {
                Log::warning('Automatic translation failed', [
                    'locale' => $locale,
                    'message' => $exception->getMessage(),
                ]);

                $translated = null;
            }

            if ($translated !== null) {
                $translations[$locale] = $translated;
            }
        }

        return $translations;
    }
}
