<?php

namespace Modules\Setting\DTOs;

use Modules\Setting\Http\Requests\UpdateAiTranslationSettingsRequest;

final readonly class UpdateAiTranslationSettingsDTO
{
    public function __construct(
        public bool $enabled,
        public string $provider,
    ) {}

    public static function fromRequest(UpdateAiTranslationSettingsRequest $request): self
    {
        return new self(
            enabled: $request->boolean('enabled'),
            provider: $request->validated('provider'),
        );
    }
}
