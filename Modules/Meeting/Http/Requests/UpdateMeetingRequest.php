<?php

namespace Modules\Meeting\Http\Requests;

use Modules\Core\Abstracts\BaseRequest;

class UpdateMeetingRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            ...$this->translatableRules('description', required: false),
            'password' => ['nullable', 'string', 'max:100'],
            'scheduled_at' => ['required', 'date'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:600'],
            'teacher_id' => ['required', 'integer', 'exists:users,id'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'academic_year_id' => ['nullable', 'integer', 'exists:academic_years,id'],
            'semester_id' => ['nullable', 'integer', 'exists:semesters,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => __('meeting::meeting.fields.title'),
            'scheduled_at' => __('meeting::meeting.fields.scheduled_at'),
            'duration_minutes' => __('meeting::meeting.fields.duration_minutes'),
            'teacher_id' => __('meeting::meeting.fields.teacher_id'),
            'branch_id' => __('meeting::meeting.fields.branch_id'),
            'academic_year_id' => __('meeting::meeting.fields.academic_year_id'),
            'semester_id' => __('meeting::meeting.fields.semester_id'),
        ];
    }
}
