<?php

namespace Modules\Timetable\Http\Requests;

use Modules\Core\Abstracts\BaseRequest;

class StoreTimetableRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'class_id' => ['required', 'integer', 'exists:classes,id'],
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
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
            'academic_year_id' => __('timetable::timetable.fields.academic_year'),
            'semester_id' => __('timetable::timetable.fields.semester'),
        ];
    }
}
