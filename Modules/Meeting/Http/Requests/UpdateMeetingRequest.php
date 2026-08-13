<?php

namespace Modules\Meeting\Http\Requests;

use Modules\Core\Abstracts\BaseRequest;

class UpdateMeetingRequest extends BaseRequest
{
    /** `meetings.title` is varchar(255); `description` is JSON, so its cap is a domain choice. */
    private const TITLE_MAX = 255;

    private const DESCRIPTION_MAX = 2000;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // `meetings.title` is varchar(255).
            'title' => ['required', 'string', 'min:3', 'max:'.self::TITLE_MAX],
            ...$this->translatableRules('description', required: false, max: self::DESCRIPTION_MAX),
            // The room password is handed to attendees verbatim, so it has to stay
            // typeable: no leading/trailing space (trimmed upstream) and a floor
            // that rules out a one-character "password".
            'password' => ['nullable', 'string', 'min:4', 'max:100'],
            'scheduled_at' => ['required', 'date'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:600'],
            'teacher_id' => ['required', 'integer', 'exists:users,id'],
            'semester_id' => ['nullable', 'integer', 'exists:semesters,id'],
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
