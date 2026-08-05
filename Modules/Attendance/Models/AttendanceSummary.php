<?php

namespace Modules\Attendance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Academic\Models\AcademicYear;
use Modules\Academic\Models\Semester;
use Modules\SchoolClass\Models\SchoolClass;
use Modules\Student\Models\StudentProfile;

class AttendanceSummary extends Model
{
    protected $table = 'attendance_summary';
    protected $guarded = ['id'];
    public $timestamps = false;
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'attendance_percentage' => 'decimal:2',
        ];
    }
    public function student(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }
    public function class(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }
}
