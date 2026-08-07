<?php

namespace Modules\Finance\Http\Requests;

use Modules\Core\Abstracts\BaseRequest;

class UpdateInvoiceRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'issue_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:issue_date'],
            'currency' => ['nullable', 'string', 'size:3'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array', 'min:1'],
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
