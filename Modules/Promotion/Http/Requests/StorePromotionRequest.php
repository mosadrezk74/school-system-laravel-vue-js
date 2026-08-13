<?php

namespace Modules\Promotion\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Abstracts\BaseRequest;
use Modules\Promotion\Enums\PromotionStatus;

class StorePromotionRequest extends BaseRequest
{
    /** A batch covers a cohort; beyond this it is a malformed payload. */
    private const MAX_DECISIONS = 2000;

    /**
     * Deliberately accepts no source academic year or class: those are read
     * from the academic context and from each student's own record, so the
     * client cannot restate where a student was.
     *
     * The target year, term and class are administrative inputs, each checked
     * for existence only. They are deliberately not cross-checked against one
     * another: this school runs standing terms and classes that recur every
     * year, so pairing them with a year is a choice, not a constraint.
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

            'to_semester_id' => ['nullable', 'integer', Rule::exists('semesters', 'id')],

            // A promotion batch covers a cohort, not the whole school twice over.
            'decisions' => ['required', 'array', 'min:1', 'max:'.self::MAX_DECISIONS],
            // `distinct` stops one student appearing twice in a batch, which would
            // otherwise write two promotion rows for the same move.
            'decisions.*.student_id' => ['required', 'integer', 'distinct', Rule::exists('student_profiles', 'id')],
            'decisions.*.status' => ['required', 'string', Rule::enum(PromotionStatus::class)],

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
            'to_semester_id' => __('promotion::promotions.fields.to_semester'),
            'decisions' => __('promotion::promotions.fields.students'),
            'decisions.*.student_id' => __('promotion::promotions.fields.student'),
            'decisions.*.status' => __('promotion::promotions.fields.status'),
            'decisions.*.target_class_id' => __('promotion::promotions.fields.to_class'),
        ];
    }
}
