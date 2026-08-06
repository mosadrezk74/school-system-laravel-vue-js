<?php

namespace Modules\Core\Services\Translation;

use Illuminate\Http\Client\Factory as HttpFactory;
use Modules\Core\Contracts\TranslationProviderInterface;

class LibreTranslateProvider implements TranslationProviderInterface
{
    public function __construct(
        private readonly HttpFactory $http,
    ) {}

    public function translate(string $text, string $sourceLocale, string $targetLocale): ?string
    {
        $url = config('services.libretranslate.url');

        if (! $url) {
            return null;
        }

        try {
            $response = $this->http->asForm()->post(rtrim($url, '/').'/translate', array_filter([
                'q' => $text,
                'source' => $sourceLocale,
                'target' => $targetLocale,
                'format' => 'text',
                'api_key' => config('services.libretranslate.api_key'),
            ]));

            if (! $response->successful()) {
                return null;
            }

            return $response->json('translatedText');
        } catch (\Throwable) {
            return null;
        }
    }
}
