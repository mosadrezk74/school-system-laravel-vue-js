<?php

namespace Modules\SchoolClass\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Academic\Models\GradeLevel;
use Modules\Classroom\Models\Classroom;
use Modules\Core\Abstracts\BaseModel;
use Modules\Student\Models\StudentProfile;

class SchoolClass extends BaseModel
{
    protected $table = 'classes';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class, 'grade_level_id');
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }

    /**
     * Students whose current class is this one — the inverse of
     * StudentProfile::currentClass().
     */
    public function students(): HasMany
    {
        return $this->hasMany(StudentProfile::class, 'current_class_id');
    }
}
