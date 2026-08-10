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
            'academic_year_id' => ['nullable', 'integer', 'exists:academic_years,id'],

            /*
             * The semester must sit inside the chosen year. Without this a
             * "Term 1 2026/2027" structure could be saved against a semester
             * from a different year and price the wrong period.
             */
            'semester_id' => [
                'nullable',
                'integer',
                Rule::exists('semesters', 'id')->where(
                    fn ($query) => $query->where('academic_year_id', $this->input('academic_year_id')),
                ),
            ],

            'is_active' => ['boolean'],
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
            'academic_year_id' => __('finance::finance.fields.academic_year_id'),
            'semester_id' => __('finance::finance.fields.semester_id'),
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
