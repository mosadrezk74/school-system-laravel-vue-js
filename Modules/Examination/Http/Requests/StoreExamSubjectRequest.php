<?php

namespace Modules\Examination\Http\Requests;

use Modules\Core\Abstracts\BaseRequest;

class StoreExamSubjectRequest extends BaseRequest
{
    /** `exam_subjects.total_marks` and `.passing_marks` are decimal(6,2). */
    private const MARKS_MAX = 9999.99;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'class_id' => ['required', 'integer', 'exists:classes,id'],
            /*
             * Both are decimal(6,2) columns, and a paper you cannot fail — a
             * pass mark above its own total — is a data-entry slip that would
             * otherwise mark every student as failed.
             */
            'total_marks' => ['required', 'numeric', 'gt:0', 'max:'.self::MARKS_MAX],
            'passing_marks' => ['required', 'numeric', 'min:0', 'max:'.self::MARKS_MAX, 'lte:total_marks'],
            'exam_date' => ['nullable', 'date'],
            'exam_time' => ['nullable', 'date_format:H:i'],
            'room' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'subject_id' => __('examination::exam.fields.subject_id'),
            'class_id' => __('examination::exam.fields.class_id'),
            'total_marks' => __('examination::exam.fields.subject_total_marks'),
            'passing_marks' => __('examination::exam.fields.passing_marks'),
        ];
    }
}
