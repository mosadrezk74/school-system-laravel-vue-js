<?php

namespace Modules\Timetable\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Setting\Models\SchoolSetting;
use Modules\Timetable\DTOs\UpdateWeekdaySettingsDTO;

class UpdateWeekdaySettingsAction extends BaseAction
{
    /**
     * @param  UpdateWeekdaySettingsDTO  $input
     */
    public function execute(mixed $input): SchoolSetting
    {
        /** @var UpdateWeekdaySettingsDTO $dto */
        $dto = $input;

        return $this->executeWithTransaction(function () use ($dto): SchoolSetting {
            $setting = SchoolSetting::query()->firstOrFail();

            $setting->update([
                'settings' => [
                    ...($setting->settings ?? []),
                    'active_weekdays' => $dto->activeWeekdays,
                ],
            ]);

            return $setting->refresh();
        });
    }
}
