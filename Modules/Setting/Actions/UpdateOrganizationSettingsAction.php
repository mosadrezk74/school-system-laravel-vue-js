<?php

namespace Modules\Setting\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Setting\DTOs\UpdateOrganizationSettingsDTO;
use Modules\Setting\Models\SchoolSetting;

class UpdateOrganizationSettingsAction extends BaseAction
{
    /**
     * @param  UpdateOrganizationSettingsDTO  $input
     */
    public function execute(mixed $input): SchoolSetting
    {
        /** @var UpdateOrganizationSettingsDTO $dto */
        $dto = $input;

        return $this->executeWithTransaction(function () use ($dto): SchoolSetting {
            $setting = SchoolSetting::query()->firstOrFail();

            $setting->update([
                'settings' => [
                    ...($setting->settings ?? []),
                    'multi_academic_year' => $dto->multiAcademicYear,
                ],
            ]);

            return $setting->refresh();
        });
    }
}
