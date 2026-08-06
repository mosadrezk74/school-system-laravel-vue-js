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
            'semester_id' => ['nullable', 'integer', 'exists:semesters,id'],
            ...$this->translatableRules('title'),
            ...$this->translatableRules('description', required: false),
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
            'semester_id' => __('assignment::assignment.fields.semester_id'),
            'title' => __('assignment::assignment.fields.title'),
            'max_score' => __('assignment::assignment.fields.max_score'),
            'deadline' => __('assignment::assignment.fields.deadline'),
        ];
    }
}
