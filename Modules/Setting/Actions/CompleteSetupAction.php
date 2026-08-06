<?php

namespace Modules\Setting\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Core\Exceptions\BaseException;
use Modules\Setting\Models\SchoolSetting;

class CompleteSetupAction extends BaseAction
{
    public function execute(mixed $input): SchoolSetting
    {
        return $this->executeWithTransaction(function (): SchoolSetting {
            $setting = SchoolSetting::query()->first();

            if (! $setting || ! $setting->school_name) {
                throw BaseException::badRequest(__('setting::onboarding.school_info_required'));
            }

            $setting->update(['setup_completed' => true]);

            return $setting->refresh();
        });
    }
}
