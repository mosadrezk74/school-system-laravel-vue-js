<?php

namespace Modules\Examination\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\SchoolClass\Models\SchoolClass;
use Modules\Subject\Models\Subject;

class ExamSubject extends Model
{
    protected $table = 'exam_subjects';

    protected $guarded = ['id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_marks' => 'decimal:2',
            'passing_marks' => 'decimal:2',
            'exam_date' => 'date',
            'exam_time' => 'string',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }
}
