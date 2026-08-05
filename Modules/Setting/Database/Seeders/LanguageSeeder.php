<?php

namespace Modules\Setting\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Setting\Models\Language;

class LanguageSeeder extends Seeder
{
    /**
     * The catalogue of languages the product ships knowing about. A school
     * turns one on by flipping is_active — and any language not listed here
     * can still be added later as a plain row; nothing in the code enumerates
     * locales.
     *
     * @var array<int, array{name: string, native: string, code: string, locale: string, direction: string}>
     */
    private const CATALOGUE = [
        ['name' => 'Arabic', 'native' => 'العربية', 'code' => 'ar', 'locale' => 'ar', 'direction' => 'rtl'],
        ['name' => 'English', 'native' => 'English', 'code' => 'en', 'locale' => 'en', 'direction' => 'ltr'],
        ['name' => 'French', 'native' => 'Français', 'code' => 'fr', 'locale' => 'fr', 'direction' => 'ltr'],
        ['name' => 'German', 'native' => 'Deutsch', 'code' => 'de', 'locale' => 'de', 'direction' => 'ltr'],
        ['name' => 'Turkish', 'native' => 'Türkçe', 'code' => 'tr', 'locale' => 'tr', 'direction' => 'ltr'],
        ['name' => 'Urdu', 'native' => 'اردو', 'code' => 'ur', 'locale' => 'ur', 'direction' => 'rtl'],
        ['name' => 'Indonesian', 'native' => 'Bahasa Indonesia', 'code' => 'id', 'locale' => 'id', 'direction' => 'ltr'],
        ['name' => 'Hindi', 'native' => 'हिन्दी', 'code' => 'hi', 'locale' => 'hi', 'direction' => 'ltr'],
        ['name' => 'Spanish', 'native' => 'Español', 'code' => 'es', 'locale' => 'es', 'direction' => 'ltr'],
        ['name' => 'Persian', 'native' => 'فارسی', 'code' => 'fa', 'locale' => 'fa', 'direction' => 'rtl'],
    ];

    /** Switched on out of the box; everything else ships disabled. */
    private const ENABLED_BY_DEFAULT = ['ar', 'en'];

    private const DEFAULT_LOCALE = 'ar';

    public function run(): void
    {
        foreach (self::CATALOGUE as $language) {
            Language::query()->updateOrCreate(
                ['locale' => $language['locale']],
                [
                    // `name` holds the endonym so the switcher reads naturally
                    // to a speaker of that language.
                    'name' => $language['native'],
                    'code' => $language['code'],
                    'direction' => $language['direction'],
                    'is_active' => in_array($language['locale'], self::ENABLED_BY_DEFAULT, true),
                    'is_default' => $language['locale'] === self::DEFAULT_LOCALE,
                ],
            );
        }

        Language::forgetCache();
    }
}
