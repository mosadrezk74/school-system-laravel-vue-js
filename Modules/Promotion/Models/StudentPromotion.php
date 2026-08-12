<?php

namespace Modules\Promotion\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Academic\Models\AcademicYear;
use Modules\Academic\Models\Semester;
use Modules\Promotion\Enums\PromotionStatus;
use Modules\SchoolClass\Models\SchoolClass;
use Modules\Student\Models\StudentProfile;

/**
 * One end-of-year decision for one student.
 *
 * Extends Model rather than BaseModel deliberately: this is an audit record,
 * so it carries no deleted_at. A promotion is undone by reverting it — which
 * writes the student back and removes the row inside one transaction — not by
 * soft-deleting the evidence and leaving the student moved.
 */
class StudentPromotion extends Model
{
    protected $table = 'student_promotions';

    protected $guarded = ['id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PromotionStatus::class,
            'was_graduated' => 'boolean',
            'promoted_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }

    public function fromAcademicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'from_academic_year_id');
    }

    public function toAcademicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'to_academic_year_id');
    }

    /**
     * The term the student was promoted into. Null for retained, graduated
     * and transferred decisions, which move nobody.
     */
    public function toSemester(): BelongsTo
    {
        return $this->belongsTo(Semester::class, 'to_semester_id');
    }

    public function fromClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'from_class_id');
    }

    public function toClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'to_class_id');
    }

    public function promotedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'promoted_by');
    }

    /**
     * @param  Builder<StudentPromotion>  $query
     */
    public function scopeForStudent(Builder $query, int $studentId): void
    {
        $query->where('student_id', $studentId);
    }

    /**
     * @param  Builder<StudentPromotion>  $query
     */
    public function scopeFromYear(Builder $query, int $academicYearId): void
    {
        $query->where('from_academic_year_id', $academicYearId);
    }
}
