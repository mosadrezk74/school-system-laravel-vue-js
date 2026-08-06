<?php

namespace Modules\Examination\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Abstracts\BaseRequest;

class StoreExamGradeRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'grade_level_id' => ['required', 'integer', 'exists:grade_levels,id'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'grade_name' => [
                'required', 'string', 'max:10',
                Rule::unique('exam_grades')->where(function ($query) {
                    $query->where('grade_level_id', $this->input('grade_level_id'))
                        ->where('subject_id', $this->input('subject_id'));
                }),
            ],
            'grade_point' => ['required', 'numeric', 'min:0'],
            'min_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'max_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'is_passing' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'grade_level_id' => __('examination::exam_grade.fields.grade_level_id'),
            'subject_id' => __('examination::exam_grade.fields.subject_id'),
            'grade_name' => __('examination::exam_grade.fields.grade_name'),
            'grade_point' => __('examination::exam_grade.fields.grade_point'),
            'min_percentage' => __('examination::exam_grade.fields.min_percentage'),
            'max_percentage' => __('examination::exam_grade.fields.max_percentage'),
        ];
    }
}
