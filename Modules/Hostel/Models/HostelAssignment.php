<?php

namespace Modules\Hostel\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Academic\Models\AcademicYear;
use Modules\Core\Abstracts\BaseModel;
use Modules\Student\Models\StudentProfile;

class HostelAssignment extends BaseModel
{
    protected $table = 'hostel_assignments';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'check_in_date' => 'date',
            'check_out_date' => 'date',
            'is_current' => 'boolean',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }

    public function hostel(): BelongsTo
    {
        return $this->belongsTo(Hostel::class, 'hostel_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(HostelRoom::class, 'room_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }
}
