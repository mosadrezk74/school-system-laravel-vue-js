<?php

namespace Modules\Examination\Http\Requests;

use Modules\Core\Abstracts\BaseRequest;

class StoreExamResultsRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'exam_id' => ['required', 'integer', 'exists:exams,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'class_id' => ['required', 'integer', 'exists:classes,id'],
            'results' => ['required', 'array', 'min:1'],
            'results.*.student_id' => ['required', 'integer', 'exists:student_profiles,id'],
            'results.*.obtained_marks' => ['nullable', 'numeric', 'min:0'],
            'results.*.remarks' => ['nullable', 'string'],
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
