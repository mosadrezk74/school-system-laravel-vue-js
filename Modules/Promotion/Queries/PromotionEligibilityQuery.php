<?php

namespace Modules\Promotion\Queries;

use Illuminate\Support\Collection;
use Modules\Examination\Queries\ReportCardQuery;
use Modules\Promotion\Models\StudentPromotion;
use Modules\SchoolClass\Models\SchoolClass;
use Modules\Student\Models\StudentProfile;

/**
 * Who is up for a promotion decision, and what the admin needs to decide.
 *
 * The project defines no automatic pass/fail rule for progression — only a
 * per-exam passing_grade — so nothing here decides anything. It surfaces each
 * student's current placement and their consolidated results, and the school
 * makes the call.
 *
 * Result figures come from ReportCardQuery, so grade bands, averages and
 * ranking are the same numbers Phase 4 already produces. None of that logic is
 * reimplemented here.
 */
class PromotionEligibilityQuery
{
    public function __construct(
        private readonly ReportCardQuery $reportCards,
    ) {}

    /**
     * Students sitting in the given year who have not yet been decided.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function eligible(int $academicYearId, int $semesterId, ?int $classId = null): Collection
    {
        $decided = StudentPromotion::query()
            ->where('from_academic_year_id', $academicYearId)
            ->pluck('student_id');

        return StudentProfile::query()
            ->with(['user', 'currentClass.gradeLevel'])
            ->where('academic_year_id', $academicYearId)

            // Graduated students have finished; they are not up for a decision.
            ->where('is_graduated', false)
            ->when($classId !== null, fn ($query) => $query->where('current_class_id', $classId))
            ->whereNotIn('id', $decided)
            ->get()
            ->map(fn (StudentProfile $student): array => $this->describe($student, $academicYearId, $semesterId))
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    private function describe(StudentProfile $student, int $academicYearId, int $semesterId): array
    {
        // Staff view: unpublished results are included, because the admin is
        // deciding progression before marks are necessarily released.
        $card = $this->reportCards->forStudent($student, $academicYearId, $semesterId);

        return [
            'student_id' => $student->id,
            'student_name' => $student->user?->name,
            'admission_number' => $student->admission_number,
            'current_class_id' => $student->current_class_id,
            'current_class_name' => $student->currentClass?->name,
            'grade_level_id' => $student->currentClass?->grade_level_id,
            'grade_level_name' => $student->currentClass?->gradeLevel?->name,
            'display_order' => $student->currentClass?->gradeLevel?->display_order,

            // Enough for the admin to judge; no threshold is applied.
            'average_percentage' => $card->averagePercentage,
            'overall_grade' => $card->overallGrade,
            'subjects_count' => count($card->subjects),
            'has_results' => $card->totalObtained !== null,
        ];
    }

    /**
     * Classes a student may be promoted into, with their remaining room.
     *
     * The *class list* is not scoped to an academic year — this school runs
     * standing classes that recur every year, so every active class is a valid
     * destination. The *occupancy* is scoped, because seats are taken per
     * year: without that, last year's cohort would make a class look full to
     * this year's intake.
     *
     * @param  int|null  $targetAcademicYearId  Year the seats are counted for.
     * @return Collection<int, array<string, mixed>>
     */
    public function targetClasses(?int $targetAcademicYearId = null): Collection
    {
        $classes = SchoolClass::query()
            ->with('gradeLevel')
            ->where('is_active', true)
            ->get();

        $counts = StudentProfile::query()
            ->whereIn('current_class_id', $classes->pluck('id'))
            ->when(
                $targetAcademicYearId !== null,
                fn ($query) => $query->where('academic_year_id', $targetAcademicYearId),
            )
            ->where('is_active', true)
            ->where('is_graduated', false)
            ->selectRaw('current_class_id, COUNT(*) as total')
            ->groupBy('current_class_id')
            ->pluck('total', 'current_class_id');

        return $classes
            ->map(function (SchoolClass $class) use ($counts): array {
                $occupied = (int) ($counts[$class->id] ?? 0);

                return [
                    'class_id' => $class->id,
                    'class_name' => $class->name,
                    'grade_level_id' => $class->grade_level_id,
                    'grade_level_name' => $class->gradeLevel?->name,
                    'display_order' => $class->gradeLevel?->display_order,
                    'capacity' => (int) $class->capacity,
                    'occupied' => $occupied,
                    'available' => max((int) $class->capacity - $occupied, 0),
                ];
            })
            ->sortBy('display_order')
            ->values();
    }
}
