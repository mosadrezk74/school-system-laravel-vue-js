<?php

namespace Modules\Setting\Http\Requests;

use Modules\Core\Abstracts\BaseRequest;

class UpdateSchoolInfoRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'school_name' => ['required', 'string', 'max:255'],
            'school_code' => ['nullable', 'string', 'max:50'],
            'timezone' => ['nullable', 'string', 'timezone'],
            'currency' => ['nullable', 'string', 'max:10'],
            'currency_symbol' => ['nullable', 'string', 'max:10'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'school_name' => __('setting::onboarding.fields.school_name'),
            'logo' => __('setting::onboarding.fields.logo'),
        ];
    }
}
