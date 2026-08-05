<?php

namespace Modules\Assignment\Http\Requests;

use Modules\Core\Abstracts\BaseRequest;

class StoreAssignmentRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'class_id' => ['required', 'integer', 'exists:classes,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'teacher_id' => ['required', 'integer', 'exists:users,id'],
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'semester_id' => ['nullable', 'integer', 'exists:semesters,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'max_score' => ['required', 'numeric', 'min:0'],
            'deadline' => ['required', 'date'],
            'assignment_type' => ['nullable', 'string', 'max:20'],
            'is_group_work' => ['sometimes', 'boolean'],
            'is_published' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'class_id' => __('assignment::assignment.fields.class_id'),
            'subject_id' => __('assignment::assignment.fields.subject_id'),
            'teacher_id' => __('assignment::assignment.fields.teacher_id'),
            'academic_year_id' => __('assignment::assignment.fields.academic_year_id'),
            'semester_id' => __('assignment::assignment.fields.semester_id'),
            'title' => __('assignment::assignment.fields.title'),
            'max_score' => __('assignment::assignment.fields.max_score'),
            'deadline' => __('assignment::assignment.fields.deadline'),
        ];
    }
}
