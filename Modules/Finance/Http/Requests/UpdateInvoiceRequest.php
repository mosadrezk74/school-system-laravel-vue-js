<?php

namespace Modules\Finance\Http\Requests;

use Modules\Core\Abstracts\BaseRequest;

class UpdateInvoiceRequest extends BaseRequest
{
    /** One invoice with more line items than this is a runaway loop, not a bill. */
    private const MAX_ITEMS = 200;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // An invoice cannot be issued for a date that has not arrived.
            'issue_date' => ['required', 'date', 'before_or_equal:today'],
            'due_date' => ['required', 'date', 'after_or_equal:issue_date'],
            // ISO 4217 is three uppercase letters; `size:3` alone accepted "1$x".
            'currency' => ['nullable', 'string', 'size:3', 'regex:/^[A-Z]{3}$/'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array', 'min:1', 'max:'.self::MAX_ITEMS],
            'items.*.fee_structure_id' => ['required', 'integer', 'exists:fee_structures,id', 'distinct'],
            'items.*.description' => ['nullable', 'array'],
            'items.*.description.*' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'issue_date' => __('finance::finance.fields.issue_date'),
            'due_date' => __('finance::finance.fields.due_date'),
            'currency' => __('finance::finance.fields.currency'),
            'notes' => __('finance::finance.fields.notes'),
            'items' => __('finance::finance.fields.items'),
        ];
    }
}
