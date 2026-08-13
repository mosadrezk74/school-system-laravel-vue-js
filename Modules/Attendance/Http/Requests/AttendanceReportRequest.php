<?php

namespace Modules\Attendance\Http\Requests;

use Modules\Core\Abstracts\BaseRequest;

class AttendanceReportRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'class_id' => ['required', 'integer', 'exists:classes,id'],
            'semester_id' => ['nullable', 'integer', 'exists:semesters,id'],
            // There is no attendance to report on for a day that has not happened.
            'date_from' => ['nullable', 'date', 'before_or_equal:today'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from', 'before_or_equal:today'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'class_id' => __('attendance::attendance.fields.class_id'),
            'semester_id' => __('attendance::attendance.fields.semester_id'),
            'date_from' => __('attendance::attendance.fields.date_from'),
            'date_to' => __('attendance::attendance.fields.date_to'),
        ];
    }
}
