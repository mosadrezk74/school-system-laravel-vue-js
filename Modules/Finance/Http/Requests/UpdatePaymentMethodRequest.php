<?php

namespace Modules\Finance\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Abstracts\BaseRequest;
use Modules\Finance\Enums\PaymentMethodType;

class UpdatePaymentMethodRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code' => ['sometimes', 'required', 'string', 'max:50'],
            'name' => ['sometimes', 'required', 'array'],
            'name.*' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.*' => ['nullable', 'string', 'max:1000'],
            'instructions' => ['nullable', 'array'],
            'instructions.*' => ['nullable', 'string', 'max:5000'],
            'type' => ['sometimes', 'required', 'string', Rule::in(PaymentMethodType::values())],
            'payment_url' => ['nullable', 'url:http,https', 'max:2048'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'account_name' => ['nullable', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:100'],
            'iban' => ['nullable', 'string', 'max:64'],
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
            'is_active' => __('finance::finance.fields.is_active'),
            'display_order' => __('finance::finance.fields.display_order'),
        ];
    }
}
