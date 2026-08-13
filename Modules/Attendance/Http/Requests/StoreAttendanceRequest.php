<?php

namespace Modules\Attendance\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Attendance\Enums\AttendanceStatus;
use Modules\Core\Abstracts\BaseRequest;

class StoreAttendanceRequest extends BaseRequest
{
    /** Matches the class capacity ceiling the SchoolClass requests enforce. */
    private const MAX_STUDENTS = 500;

    /** `attendance_records.session` is a tinyint; no timetable runs more than this in a day. */
    private const MAX_SESSIONS = 20;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'class_id' => ['required', 'integer', 'exists:classes,id'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'semester_id' => ['nullable', 'integer', 'exists:semesters,id'],
            // Attendance is recorded for a day that has happened; `session` is a
            // tinyint and no school runs more than a couple of dozen a day.
            'date' => ['required', 'date', 'before_or_equal:today'],
            'session' => ['required', 'integer', 'min:1', 'max:'.self::MAX_SESSIONS],

            // One submission covers one class, and no class is larger than this.
            'students' => ['required', 'array', 'min:1', 'max:'.self::MAX_STUDENTS],
            // `distinct` stops the same student appearing twice in one submission,
            // which would otherwise write two rows and double-count the day.
            'students.*.student_id' => ['required', 'integer', 'distinct', 'exists:student_profiles,id'],
            'students.*.status' => ['required', 'string', Rule::enum(AttendanceStatus::class)],
            'students.*.notes' => ['nullable', 'string', 'max:500'],
            'students.*.check_in_time' => ['nullable', 'date_format:H:i'],
            'students.*.check_out_time' => ['nullable', 'date_format:H:i', 'after:students.*.check_in_time'],
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
            'semester_id' => __('attendance::attendance.fields.semester_id'),
            'date' => __('attendance::attendance.fields.date'),
            'session' => __('attendance::attendance.fields.session'),
            'students.*.status' => __('attendance::attendance.fields.status'),
            'students.*.notes' => __('attendance::attendance.fields.notes'),
        ];
    }
}
