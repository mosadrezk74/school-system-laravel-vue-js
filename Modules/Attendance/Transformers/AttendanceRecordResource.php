<?php

namespace Modules\Attendance\Transformers;

use Illuminate\Http\Request;
use Modules\Attendance\Models\AttendanceRecord;
use Modules\Core\Abstracts\BaseResource;

/**
 * @mixin AttendanceRecord
 */
class AttendanceRecordResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'class_id' => $this->class_id,
            'subject_id' => $this->subject_id,
            'academic_year_id' => $this->academic_year_id,
            'semester_id' => $this->semester_id,
            'date' => $this->date?->toDateString(),
            'session' => $this->session,
            'present_count' => $this->present_count,
            'absent_count' => $this->absent_count,
            'late_count' => $this->late_count,
            'excused_count' => $this->excused_count,
            'created_at' => $this->created_at?->toIso8601String(),

            'class' => $this->whenLoaded('class', fn () => $this->class ? [
                'id' => $this->class->id,
                'name' => $this->class->name,
                'section_code' => $this->class->section_code,
            ] : null),

            'subject' => $this->whenLoaded('subject', fn () => $this->subject ? [
                'id' => $this->subject->id,
                'name' => $this->subject->name,
            ] : null),

            'academic_year' => $this->whenLoaded('academicYear', fn () => $this->academicYear ? [
                'id' => $this->academicYear->id,
                'name' => $this->academicYear->name,
            ] : null),

            'semester' => $this->whenLoaded('semester', fn () => $this->semester ? [
                'id' => $this->semester->id,
                'name' => $this->semester->name,
            ] : null),

            'details' => $this->whenLoaded('details', fn () => $this->details->map(fn ($detail) => [
                'id' => $detail->id,
                'student_id' => $detail->student_id,
                'student_name' => $detail->student?->user?->name,
                'admission_number' => $detail->student?->admission_number,
                'status' => $detail->status,
                'check_in_time' => $detail->check_in_time,
                'check_out_time' => $detail->check_out_time,
            ])->all()),
        ];
    }
}
