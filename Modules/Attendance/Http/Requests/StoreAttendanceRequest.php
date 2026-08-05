<?php

namespace Modules\Attendance\Http\Requests;

use Modules\Core\Abstracts\BaseRequest;

class StoreAttendanceRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'class_id' => ['required', 'integer', 'exists:classes,id'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'semester_id' => ['nullable', 'integer', 'exists:semesters,id'],
            'date' => ['required', 'date'],
            'session' => ['required', 'integer', 'min:1'],

            'students' => ['required', 'array', 'min:1'],
            'students.*.student_id' => ['required', 'integer', 'exists:student_profiles,id'],
            'students.*.status' => ['required', 'string', 'in:present,absent,late,excused'],
            'students.*.check_in_time' => ['nullable', 'date_format:H:i'],
            'students.*.check_out_time' => ['nullable', 'date_format:H:i'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'class_id' => __('attendance::attendance.fields.class_id'),
            'subject_id' => __('attendance::attendance.fields.subject_id'),
            'academic_year_id' => __('attendance::attendance.fields.academic_year_id'),
            'semester_id' => __('attendance::attendance.fields.semester_id'),
            'date' => __('attendance::attendance.fields.date'),
            'session' => __('attendance::attendance.fields.session'),
        ];
    }
}
