<?php

namespace Modules\Examination\Http\Requests;

use Modules\Core\Abstracts\BaseRequest;

class StoreExamResultsRequest extends BaseRequest
{
    /** Matches the class capacity ceiling the SchoolClass requests enforce. */
    private const MAX_RESULTS = 500;

    /** `exam_results.obtained_marks` is decimal(6,2); `remarks` is text, so its cap is a domain choice. */
    private const MARKS_MAX = 9999.99;

    private const REMARKS_MAX = 1000;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'exam_id' => ['required', 'integer', 'exists:exams,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'class_id' => ['required', 'integer', 'exists:classes,id'],
            // One submission covers one class sitting one paper.
            'results' => ['required', 'array', 'min:1', 'max:'.self::MAX_RESULTS],
            'results.*.student_id' => ['required', 'integer', 'distinct', 'exists:student_profiles,id'],
            /*
             * `exam_results.obtained_marks` is decimal(6,2) and `remarks` is a
             * text column that had no cap at all, so a paste of a whole essay
             * went straight into the database.
             */
            'results.*.obtained_marks' => ['nullable', 'numeric', 'min:0', 'max:'.self::MARKS_MAX],
            'results.*.remarks' => ['nullable', 'string', 'max:'.self::REMARKS_MAX],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'exam_id' => __('examination::exam.fields.exam_id'),
            'subject_id' => __('examination::exam.fields.subject_id'),
            'class_id' => __('examination::exam.fields.class_id'),
            'results' => __('examination::exam.fields.results'),
        ];
    }
}
