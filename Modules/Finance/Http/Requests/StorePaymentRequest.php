<?php

namespace Modules\Finance\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Abstracts\BaseRequest;
use Modules\Finance\Enums\PaymentMethod;

class StorePaymentRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'invoice_id' => ['required', 'integer', 'exists:invoices,id'],
            'amount' => ['required', 'numeric', 'gt:0', 'max:99999999.99'],
            'payment_method' => ['required', 'string', Rule::in(PaymentMethod::values())],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'paid_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'invoice_id' => __('finance::finance.fields.invoice_id'),
            'amount' => __('finance::finance.fields.amount'),
            'payment_method' => __('finance::finance.fields.payment_method'),
            'reference_number' => __('finance::finance.fields.reference_number'),
            'paid_at' => __('finance::finance.fields.paid_at'),
            'notes' => __('finance::finance.fields.notes'),
        ];
    }
}
