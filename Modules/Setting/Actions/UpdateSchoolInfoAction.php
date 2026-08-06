<?php

namespace Modules\Setting\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Setting\DTOs\UpdateSchoolInfoDTO;
use Modules\Setting\Models\SchoolSetting;

class UpdateSchoolInfoAction extends BaseAction
{
    /**
     * @param  UpdateSchoolInfoDTO  $input
     */
    public function execute(mixed $input): SchoolSetting
    {
        /** @var UpdateSchoolInfoDTO $dto */
        $dto = $input;

        return $this->executeWithTransaction(function () use ($dto): SchoolSetting {
            $setting = SchoolSetting::query()->first() ?? new SchoolSetting;

            $data = [
                'school_name' => $dto->schoolName,
                'school_code' => $dto->schoolCode ?? $setting->school_code ?? strtoupper(str()->random(6)),
                'timezone' => $dto->timezone ?? $setting->timezone ?? 'Asia/Riyadh',
                'currency' => $dto->currency ?? $setting->currency ?? 'SAR',
                'currency_symbol' => $dto->currencySymbol ?? $setting->currency_symbol ?? '﷼',
                'is_active' => true,
            ];

            if ($dto->logo) {
                $data['logo_path'] = $dto->logo->store('school', 'public');
            }

            $setting->fill($data);
            $setting->save();

            return $setting->refresh();
        });
    }
}
