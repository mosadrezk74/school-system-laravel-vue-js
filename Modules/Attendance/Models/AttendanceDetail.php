<?php

namespace Modules\Attendance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Attendance\Models\AttendanceRecord;
use Modules\Student\Models\StudentProfile;

class AttendanceDetail extends Model
{
    protected $table = 'attendance_details';
    protected $guarded = ['id'];
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'check_in_time' => 'string',
            'check_out_time' => 'string',
        ];
    }
    public function attendanceRecord(): BelongsTo
    {
        return $this->belongsTo(AttendanceRecord::class, 'attendance_record_id');
    }
    public function student(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }
}
