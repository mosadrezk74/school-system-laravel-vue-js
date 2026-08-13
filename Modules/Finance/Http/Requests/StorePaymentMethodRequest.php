<?php

namespace Modules\Finance\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Abstracts\BaseRequest;
use Modules\Finance\Enums\PaymentMethodType;

class StorePaymentMethodRequest extends BaseRequest
{
    /** `payment_methods.payment_url` is varchar(255), not the 2048 this rule used to allow. */
    private const URL_MAX = 255;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9_-]+$/'],
            'name' => ['required', 'array'],
            'name.*' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.*' => ['nullable', 'string', 'max:1000'],
            'instructions' => ['nullable', 'array'],
            'instructions.*' => ['nullable', 'string', 'max:5000'],
            'type' => ['required', 'string', Rule::enum(PaymentMethodType::class)],

            /*
             * A parent is sent to this URL, so only absolute http(s) is
             * accepted — a javascript: or relative value here would be a
             * redirect the school did not intend.
             */
            // `payment_methods.payment_url` is varchar(255): the previous 2048 was
            // silently truncated by MySQL, leaving a broken link for parents.
            'payment_url' => ['nullable', 'url:http,https', 'max:'.self::URL_MAX],

            'bank_name' => ['nullable', 'string', 'max:255'],
            'account_name' => ['nullable', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9-]+$/'],
            // IBANs are uppercase alphanumerics: two letters, two check digits, then
            // up to 30 more characters. Spaces are stripped before this runs.
            'iban' => ['nullable', 'string', 'max:64', 'regex:/^[A-Z]{2}[0-9]{2}[A-Z0-9]{1,30}$/'],
            'is_active' => ['boolean'],
            'display_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'code' => __('finance::finance.fields.code'),
            'name' => __('finance::finance.fields.name'),
            'type' => __('finance::finance.fields.type'),
            'payment_url' => __('finance::finance.fields.payment_url'),
            'bank_name' => __('finance::finance.fields.bank_name'),
            'account_name' => __('finance::finance.fields.account_name'),
            'account_number' => __('finance::finance.fields.account_number'),
            'iban' => __('finance::finance.fields.iban'),
            'is_active' => __('finance::finance.fields.is_active'),
            'display_order' => __('finance::finance.fields.display_order'),
        ];
    }
}
