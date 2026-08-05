<?php

namespace Modules\Assignment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Student\Models\StudentProfile;

class AssignmentSubmission extends Model
{
    protected $table = 'assignment_submissions';

    protected $guarded = ['id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'score' => 'decimal:2',
            'is_late' => 'boolean',
            'is_graded' => 'boolean',
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class, 'assignment_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }
}
