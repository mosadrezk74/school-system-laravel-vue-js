<?php

namespace Modules\Attendance\Transformers;

use Illuminate\Http\Request;
use Modules\Attendance\Models\AttendanceDetail;
use Modules\Core\Abstracts\BaseResource;

/**
 * @mixin AttendanceDetail
 */
class StudentAttendanceTimelineResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->attendanceRecord?->date?->toDateString(),
            'session' => $this->attendanceRecord?->session,
            'status' => $this->status?->value,
            'notes' => $this->notes,
            'teacher' => $this->attendanceRecord?->createdBy?->name,
        ];
    }
}
