<?php

namespace Modules\Core\Contracts;

interface TranslationProviderInterface
{
    public function translate(string $text, string $sourceLocale, string $targetLocale): ?string;

    public function detect(string $text): ?string;
}
