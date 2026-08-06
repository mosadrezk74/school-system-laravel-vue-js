<?php

namespace Modules\Communication\DTOs;

use Modules\Communication\Http\Requests\StoreEmailBroadcastRequest;
use Modules\Setting\Services\LanguageService;

final readonly class SendEmailBroadcastDTO
{
    /**
     * @param  array<string, string|null>  $subject
     * @param  array<string, string|null>  $body
     * @param  array<int, int|string>|null  $targetIds
     */
    public function __construct(
        public array $subject,
        public array $body,
        public string $fallbackLocale,
        public string $targetType,
        public ?array $targetIds,
    ) {}

    public static function fromRequest(StoreEmailBroadcastRequest $request): self
    {
        return new self(
            subject: $request->validated('subject'),
            body: $request->validated('body'),
            fallbackLocale: app(LanguageService::class)->defaultLocale(),
            targetType: $request->validated('target_type'),
            targetIds: $request->validated('target_ids'),
        );
    }

    /**
     * The subject in the recipient's own language, falling back to the
     * school default and then to whatever translation exists.
     */
    public function subjectFor(?string $locale): string
    {
        return $this->translationFor($this->subject, $locale);
    }

    public function bodyFor(?string $locale): string
    {
        return $this->translationFor($this->body, $locale);
    }

    /**
     * @param  array<string, string|null>  $translations
     */
    private function translationFor(array $translations, ?string $locale): string
    {
        $candidates = array_filter([$locale, $this->fallbackLocale]);

        foreach ($candidates as $candidate) {
            if (! empty($translations[$candidate])) {
                return $translations[$candidate];
            }
        }

        foreach ($translations as $translation) {
            if (! empty($translation)) {
                return $translation;
            }
        }

        return '';
    }
}
