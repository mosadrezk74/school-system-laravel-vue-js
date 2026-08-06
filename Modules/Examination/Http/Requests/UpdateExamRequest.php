<?php

namespace Modules\Examination\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Abstracts\BaseRequest;

class UpdateExamRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $exam = $this->route('exam');

        return [
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'semester_id' => ['nullable', 'integer', 'exists:semesters,id'],
            'class_id' => ['nullable', 'integer', 'exists:classes,id'],
            ...$this->translatableRules('name'),
            'code' => ['nullable', 'string', 'max:50', Rule::unique('exams', 'code')->ignore($exam?->id)],
            'exam_type' => ['required', 'string', 'max:20'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'passing_grade' => ['nullable', 'numeric', 'min:0'],
            'total_marks' => ['nullable', 'numeric', 'min:0'],
            'weightage' => ['nullable', 'numeric', 'min:0'],
            'is_published' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'academic_year_id' => __('examination::exam.fields.academic_year_id'),
            'semester_id' => __('examination::exam.fields.semester_id'),
            'class_id' => __('examination::exam.fields.class_id'),
            'name' => __('examination::exam.fields.name'),
            'code' => __('examination::exam.fields.code'),
            'exam_type' => __('examination::exam.fields.exam_type'),
            'start_date' => __('examination::exam.fields.start_date'),
            'end_date' => __('examination::exam.fields.end_date'),
        ];
    }
}
