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
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
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
