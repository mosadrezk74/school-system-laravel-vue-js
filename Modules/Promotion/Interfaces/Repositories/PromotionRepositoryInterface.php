<?php

namespace Modules\Promotion\Interfaces\Repositories;

use Illuminate\Support\Collection;
use Modules\Promotion\DTOs\PromotionDecisionDTO;
use Modules\Promotion\Models\StudentPromotion;
use Modules\Student\Models\StudentProfile;

interface PromotionRepositoryInterface
{
    public function findById(int $id): ?StudentPromotion;

    /**
     * Students already decided for the given source year, keyed by student id,
     * so a bulk run can reject duplicates without a query per student.
     *
     * @param  array<int, int>  $studentIds
     * @return Collection<int, StudentPromotion>
     */
    public function existingDecisions(array $studentIds, int $fromAcademicYearId): Collection;

    /** The most recent decision recorded for a student. */
    public function latestForStudent(int $studentId): ?StudentPromotion;

    /**
     * Live students occupying a class. Soft-deleted rows are excluded, so a
     * withdrawn student never blocks a seat.
     *
     * @param  array<int, int>  $classIds
     * @return array<int, int> Count keyed by class id.
     */
    public function activeCountsByClass(array $classIds): array;

    public function record(
        StudentProfile $student,
        PromotionDecisionDTO $decision,
        int $fromAcademicYearId,
        ?int $toAcademicYearId,
        ?int $actorId,
    ): StudentPromotion;

    /** Applies the decision to the student's own academic state. */
    public function applyToStudent(
        StudentProfile $student,
        PromotionDecisionDTO $decision,
        ?int $toAcademicYearId,
    ): StudentProfile;

    /** Writes the student back to the state the promotion row captured. */
    public function restoreFrom(StudentPromotion $promotion): StudentProfile;

    public function delete(StudentPromotion $promotion): bool;
}
