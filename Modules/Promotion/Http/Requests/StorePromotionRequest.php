<?php

namespace Modules\Promotion\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Abstracts\BaseRequest;
use Modules\Promotion\Enums\PromotionStatus;

class StorePromotionRequest extends BaseRequest
{
    /**
     * Deliberately accepts no source academic year or class: those are read
     * from the academic context and from each student's own record, so the
     * client cannot restate where a student was.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            /*
             * Required only when the batch actually moves someone; a batch of
             * retentions has no target. The Action enforces that rule, since
             * it depends on the decisions themselves.
             */
            'to_academic_year_id' => ['nullable', 'integer', Rule::exists('academic_years', 'id')],

            'decisions' => ['required', 'array', 'min:1'],
            'decisions.*.student_id' => ['required', 'integer', Rule::exists('student_profiles', 'id')],
            'decisions.*.status' => ['required', 'string', Rule::in(PromotionStatus::values())],
            'decisions.*.target_class_id' => ['nullable', 'integer', Rule::exists('classes', 'id')],
            'decisions.*.remarks' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'to_academic_year_id' => __('promotion::promotions.fields.to_academic_year'),
            'decisions' => __('promotion::promotions.fields.students'),
            'decisions.*.student_id' => __('promotion::promotions.fields.student'),
            'decisions.*.status' => __('promotion::promotions.fields.status'),
            'decisions.*.target_class_id' => __('promotion::promotions.fields.to_class'),
        ];
    }
}
