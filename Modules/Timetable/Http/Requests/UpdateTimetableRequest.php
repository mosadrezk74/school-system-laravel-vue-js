<?php

namespace Modules\Timetable\Http\Requests;

use Modules\Core\Abstracts\BaseRequest;

class UpdateTimetableRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'class_id' => ['required', 'integer', 'exists:classes,id'],
            'semester_id' => ['nullable', 'integer', 'exists:semesters,id'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'class_id' => __('timetable::timetable.fields.class'),
            'semester_id' => __('timetable::timetable.fields.semester'),
        ];
    }
}
