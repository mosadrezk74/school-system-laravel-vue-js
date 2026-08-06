<?php

namespace Modules\Timetable\Http\Requests;

use Modules\Core\Abstracts\BaseRequest;

class UpdateTimetableEntriesRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'entries' => ['present', 'array'],
            'entries.*.day_of_week' => ['required', 'integer', 'between:0,6'],
            'entries.*.period_id' => ['required', 'integer', 'exists:periods,id'],
            'entries.*.is_break' => ['sometimes', 'boolean'],
            'entries.*.subject_id' => ['required_if:entries.*.is_break,false', 'nullable', 'integer', 'exists:subjects,id'],
            'entries.*.teacher_id' => ['required_if:entries.*.is_break,false', 'nullable', 'integer', 'exists:users,id'],
            'entries.*.classroom_id' => ['nullable', 'integer', 'exists:classrooms,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'entries.*.subject_id' => __('timetable::timetable.fields.subject'),
            'entries.*.teacher_id' => __('timetable::timetable.fields.teacher'),
            'entries.*.classroom_id' => __('timetable::timetable.fields.classroom'),
        ];
    }
}
