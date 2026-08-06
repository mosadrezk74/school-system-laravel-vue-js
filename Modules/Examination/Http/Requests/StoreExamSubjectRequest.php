<?php

namespace Modules\Examination\Http\Requests;

use Modules\Core\Abstracts\BaseRequest;

class StoreExamSubjectRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'class_id' => ['required', 'integer', 'exists:classes,id'],
            'total_marks' => ['required', 'numeric', 'min:0'],
            'passing_marks' => ['required', 'numeric', 'min:0'],
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
