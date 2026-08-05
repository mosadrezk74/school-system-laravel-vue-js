<?php

namespace Modules\Examination\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Academic\Models\AcademicYear;
use Modules\Academic\Models\Semester;
use Modules\Core\Abstracts\BaseModel;
use Modules\SchoolClass\Models\SchoolClass;

class Exam extends BaseModel
{
    protected $table = 'exams';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'passing_grade' => 'decimal:2',
            'total_marks' => 'decimal:2',
            'weightage' => 'decimal:2',
            'is_published' => 'boolean',
        ];
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

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
