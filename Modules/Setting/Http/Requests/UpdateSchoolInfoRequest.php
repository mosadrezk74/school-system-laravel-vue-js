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
            'school_name' => ['required', 'string', 'min:2', 'max:255'],
            'school_code' => ['nullable', 'string', 'max:50', 'regex:/^[A-Za-z0-9_-]+$/'],
            'timezone' => ['nullable', 'string', 'timezone'],

            /*
             * ISO 4217, three uppercase letters. `max:10` accepted anything at
             * all, and this value is what every invoice and receipt is priced
             * in, so a stray character propagates through the whole ledger.
             */
            'currency' => ['nullable', 'string', 'size:3', 'regex:/^[A-Z]{3}$/'],
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
