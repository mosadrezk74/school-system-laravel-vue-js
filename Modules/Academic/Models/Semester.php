<?php

namespace Modules\Academic\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Academic\Models\AcademicYear;
use Modules\Core\Abstracts\BaseModel;

class Semester extends BaseModel
{
    protected $table = 'semesters';
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'exam_start_date' => 'date',
            'exam_end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }
}
