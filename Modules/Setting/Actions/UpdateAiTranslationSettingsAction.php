<?php

namespace Modules\Setting\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Setting\DTOs\UpdateAiTranslationSettingsDTO;
use Modules\Setting\Models\SchoolSetting;

class UpdateAiTranslationSettingsAction extends BaseAction
{
    /**
     * @param  UpdateAiTranslationSettingsDTO  $input
     */
    public function execute(mixed $input): SchoolSetting
    {
        /** @var UpdateAiTranslationSettingsDTO $dto */
        $dto = $input;

        return $this->executeWithTransaction(function () use ($dto): SchoolSetting {
            $setting = SchoolSetting::query()->firstOrFail();

            $setting->update([
                'settings' => [
                    ...($setting->settings ?? []),
                    'ai_translation_enabled' => $dto->enabled,
                    'ai_translation_provider' => $dto->provider,
                ],
            ]);

            return $setting->refresh();
        });
    }
}
