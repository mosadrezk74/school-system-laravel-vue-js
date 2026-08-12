<?php

namespace Modules\Promotion\Services;

use Illuminate\Support\Collection;
use Modules\Academic\Models\AcademicYear;
use Modules\Academic\Models\Semester;
use Modules\Promotion\DTOs\PromotionDecisionDTO;
use Modules\Promotion\Exceptions\PromotionOperationException;
use Modules\Promotion\Models\StudentPromotion;
use Modules\SchoolClass\Models\SchoolClass;
use Modules\Student\Models\StudentProfile;

/**
 * Every rule that decides whether a promotion may go ahead.
 *
 * Kept apart from the Action so the whole rule set can be validated for every
 * student *before* a single row is written — which is what makes a bulk run
 * all-or-nothing rather than partially applied.
 */
class PromotionGuardService
{
    /**
     * The target year must exist, be active, and not be the year the students
     * are being promoted out of.
     */
    public function assertTargetYear(?int $targetYearId, int $sourceYearId): AcademicYear
    {
        if ($targetYearId === null) {
            throw PromotionOperationException::targetYearInvalid();
        }

        if ($targetYearId === $sourceYearId) {
            throw PromotionOperationException::targetYearSameAsSource();
        }

        $year = AcademicYear::query()->whereKey($targetYearId)->first();

        if (! $year instanceof AcademicYear || ! $year->is_active) {
            throw PromotionOperationException::targetYearInvalid();
        }

        return $year;
    }

    /**
     * The target term must exist and be active.
     *
     * Deliberately *not* checked against the target academic year: this school
     * treats terms and classes as standing structures that recur every year,
     * so pairing them with a year is an administrative choice rather than a
     * constraint. The chosen year, term and class are each recorded as picked.
     */
    public function assertTargetSemester(?int $targetSemesterId): Semester
    {
        if ($targetSemesterId === null) {
            throw PromotionOperationException::targetSemesterRequired();
        }

        $semester = Semester::query()->whereKey($targetSemesterId)->first();

        if (! $semester instanceof Semester || ! $semester->is_active) {
            throw PromotionOperationException::targetSemesterInvalid();
        }

        return $semester;
    }

    /** The student must be sitting in the year the decision is being made for. */
    public function assertStudentInSourceYear(StudentProfile $student, int $sourceYearId): void
    {
        if ((int) $student->academic_year_id !== $sourceYearId) {
            throw PromotionOperationException::studentNotInSourceYear();
        }
    }

    public function assertNotGraduated(StudentProfile $student): void
    {
        if ($student->is_graduated) {
            throw PromotionOperationException::studentAlreadyGraduated();
        }
    }

    /**
     * @param  Collection<int, StudentPromotion>  $existing  Keyed by student id.
     */
    public function assertNotAlreadyDecided(StudentProfile $student, Collection $existing): void
    {
        if ($existing->has($student->id)) {
            throw PromotionOperationException::studentAlreadyDecided();
        }
    }

    /**
     * The target class must exist, be active, and represent a grade the
     * student is not moving backwards into.
     *
     * Its academic year is not checked, for the same reason as the term above:
     * classes here are standing structures that carry across years.
     */
    public function assertTargetClass(
        PromotionDecisionDTO $decision,
        StudentProfile $student,
    ): SchoolClass {
        if ($decision->targetClassId === null) {
            throw PromotionOperationException::targetClassRequired();
        }

        $class = SchoolClass::query()
            ->with('gradeLevel')
            ->whereKey($decision->targetClassId)
            ->first();

        if (! $class instanceof SchoolClass || ! $class->is_active) {
            throw PromotionOperationException::targetClassInvalid();
        }

        $this->assertGradeProgression($student, $class);

        return $class;
    }

    /**
     * A promotion may repeat a grade level (a school can move a student to a
     * different section at the same level) but never move them down one.
     * Ordering comes from grade_levels.display_order, which the schema
     * already maintains and indexes.
     */
    private function assertGradeProgression(StudentProfile $student, SchoolClass $targetClass): void
    {
        $currentOrder = $student->currentClass?->gradeLevel?->display_order;
        $targetOrder = $targetClass->gradeLevel?->display_order;

        if ($currentOrder === null || $targetOrder === null) {
            return;
        }

        if ((int) $targetOrder < (int) $currentOrder) {
            throw PromotionOperationException::gradeLevelRegression();
        }
    }

    /**
     * Rejects a class that the run would push past its capacity.
     *
     * $incoming counts students this same run is placing into the class, so a
     * bulk promotion cannot overfill a class one student at a time.
     *
     * @param  array<int, int>  $currentCounts  Live occupancy keyed by class id.
     */
    public function assertCapacity(SchoolClass $class, array $currentCounts, int $incoming): void
    {
        $capacity = (int) $class->capacity;

        if ($capacity <= 0) {
            return;
        }

        $occupied = $currentCounts[$class->id] ?? 0;

        if ($occupied + $incoming > $capacity) {
            throw PromotionOperationException::targetClassFull();
        }
    }

    /**
     * A promotion may only be undone if nothing has happened to the student
     * since — otherwise the revert would write them back over a later move.
     */
    public function assertRevertable(StudentPromotion $promotion, ?StudentPromotion $latest): void
    {
        if ($latest !== null && $latest->id !== $promotion->id) {
            throw PromotionOperationException::supersededByNewerPromotion();
        }
    }
}
