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
     * The language the user actually typed in, detected from the raw text
     * rather than assumed from the current UI locale. Falls back to the app
     * locale when detection is unavailable or returns an inactive language.
     */
    public function detectSourceLocale(string $text): string
    {
        $detected = trim($text) !== '' ? $this->provider->detect($text) : null;

        if ($detected && $this->languages->isSupported($detected)) {
            return $detected;
        }

        return app()->getLocale();
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
