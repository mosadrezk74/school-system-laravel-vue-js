<?php

namespace Modules\Finance\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Abstracts\BaseRequest;
use Modules\Finance\Enums\FeeFrequency;

class StoreFeeStructureRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            ...$this->translatableRules('name'),
            'fee_category_id' => ['required', 'integer', 'exists:fee_categories,id'],
            'grade_level_id' => ['nullable', 'integer', 'exists:grade_levels,id', 'prohibits:classroom_id'],
            'classroom_id' => ['nullable', 'integer', 'exists:classrooms,id'],
            'amount' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'frequency' => ['required', 'string', Rule::in(FeeFrequency::values())],
            'is_optional' => ['boolean'],
            'due_day' => ['nullable', 'integer', 'min:1', 'max:31'],
            'late_fee_amount' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'late_fee_days' => ['nullable', 'integer', 'min:0', 'max:127'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => __('finance::finance.fields.name'),
            'fee_category_id' => __('finance::finance.fields.fee_category_id'),
            'grade_level_id' => __('finance::finance.fields.grade_level_id'),
            'classroom_id' => __('finance::finance.fields.classroom_id'),
            'amount' => __('finance::finance.fields.amount'),
            'frequency' => __('finance::finance.fields.frequency'),
            'is_optional' => __('finance::finance.fields.is_optional'),
            'due_day' => __('finance::finance.fields.due_day'),
            'late_fee_amount' => __('finance::finance.fields.late_fee_amount'),
            'late_fee_days' => __('finance::finance.fields.late_fee_days'),
        ];
    }
}
