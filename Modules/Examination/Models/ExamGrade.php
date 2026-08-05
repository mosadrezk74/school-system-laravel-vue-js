<?php

namespace Modules\Examination\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Academic\Models\GradeLevel;
use Modules\Subject\Models\Subject;

class ExamGrade extends Model
{
    protected $table = 'exam_grades';

    protected $guarded = ['id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'grade_point' => 'decimal:2',
            'min_percentage' => 'decimal:2',
            'max_percentage' => 'decimal:2',
            'is_passing' => 'boolean',
        ];
    }

    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class, 'grade_level_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
}
