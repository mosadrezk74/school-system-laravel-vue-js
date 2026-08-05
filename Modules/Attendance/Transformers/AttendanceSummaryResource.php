<?php

namespace Modules\Attendance\Transformers;

use Illuminate\Http\Request;
use Modules\Attendance\Models\AttendanceSummary;
use Modules\Core\Abstracts\BaseResource;

/**
 * @mixin AttendanceSummary
 */
class AttendanceSummaryResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'academic_year_id' => $this->academic_year_id,
            'semester_id' => $this->semester_id,
            'class_id' => $this->class_id,
            'total_days' => $this->total_days,
            'present_days' => $this->present_days,
            'absent_days' => $this->absent_days,
            'late_days' => $this->late_days,
            'attendance_percentage' => $this->attendance_percentage,
        ];
    }
}
