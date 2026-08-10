<?php

namespace Modules\Finance\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Modules\Academic\Models\AcademicYear;
use Modules\Academic\Models\GradeLevel;
use Modules\Academic\Models\Semester;
use Modules\Classroom\Models\Classroom;
use Modules\Core\Abstracts\BaseModel;
use Modules\Finance\Enums\FeeFrequency;
use Spatie\Translatable\HasTranslations;

class FeeStructure extends BaseModel
{
    use HasTranslations;

    /** @var array<int, string> */
    public array $translatable = ['name'];

    protected $table = 'fee_structures';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'frequency' => FeeFrequency::class,
            'is_optional' => 'boolean',
            'is_active' => 'boolean',
            'late_fee_amount' => 'decimal:2',
        ];
    }

    public function feeCategory(): BelongsTo
    {
        return $this->belongsTo(FeeCategory::class, 'fee_category_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class, 'grade_level_id');
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }

    public function studentFees(): HasMany
    {
        return $this->hasMany(StudentFee::class, 'fee_structure_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /** A term subscription: priced for one semester of an academic year. */
    public function isTermSubscription(): bool
    {
        return $this->semester_id !== null;
    }

    /** A full-year subscription: tied to the year with no single semester. */
    public function isFullYearSubscription(): bool
    {
        return $this->academic_year_id !== null && $this->semester_id === null;
    }

    /**
     * The period this structure covers, taken from the semester or academic
     * year it points at. Nothing about the length is stored here, so changing
     * a term's dates changes the subscription period with it.
     *
     * @return array{start: ?Carbon, end: ?Carbon}
     */
    public function subscriptionPeriod(): array
    {
        $source = $this->semester ?? $this->academicYear;

        return [
            'start' => $source?->start_date,
            'end' => $source?->end_date,
        ];
    }

    /**
     * Whole months covered by the period, derived rather than configured.
     */
    public function durationInMonths(): ?int
    {
        ['start' => $start, 'end' => $end] = $this->subscriptionPeriod();

        if (! $start || ! $end) {
            return null;
        }

        /*
         * Rounded, not truncated: a term running 01/09–31/12 is four months to
         * anyone reading it, but the raw diff is 3.97 and would floor to three.
         */
        return max(1, (int) round($start->diffInMonths($end)));
    }
}
