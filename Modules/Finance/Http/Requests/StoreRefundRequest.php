<?php

namespace Modules\Finance\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Abstracts\BaseRequest;
use Modules\Finance\Enums\PaymentMethod;

/**
 * The shape check only. Whether the amount actually fits inside what remains
 * refundable is decided by RecordRefundAction under a row lock, because that
 * ceiling can move between validating and writing.
 */
class StoreRefundRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'payment_id' => ['required', 'integer', 'exists:payments,id'],
            'amount' => ['required', 'numeric', 'gt:0', 'max:99999999.99'],
            'refund_date' => ['required', 'date'],
            'refund_method' => ['nullable', 'string', Rule::in(PaymentMethod::values())],
            'reason' => ['nullable', 'string', 'max:5000'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'payment_id' => __('finance::finance.fields.payment_id'),
            'amount' => __('finance::finance.fields.amount'),
            'refund_date' => __('finance::finance.fields.refund_date'),
            'refund_method' => __('finance::finance.fields.refund_method'),
            'reason' => __('finance::finance.fields.reason'),
            'notes' => __('finance::finance.fields.notes'),
        ];
    }
}
