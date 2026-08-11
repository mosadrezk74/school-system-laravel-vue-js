<?php

namespace Modules\Meeting\Http\Requests;

use Modules\Core\Abstracts\BaseRequest;

class StoreMeetingRequest extends BaseRequest
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
            'semester_id' => ['nullable', 'integer', 'exists:semesters,id'],

            /*
             * The forms have always submitted these two, but they were never
             * validated and never reached the DTO, so every meeting was stored
             * unscoped. They carry the class/subject a teacher may only pick
             * from their own assignments — TeacherStoreMeetingRequest enforces
             * that intersection.
             */
            'school_class_id' => ['nullable', 'integer', 'exists:classes,id'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
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
            'semester_id' => __('meeting::meeting.fields.semester_id'),
        ];
    }
}
