<?php

namespace Modules\Promotion\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Promotion\DTOs\PromotionDecisionDTO;
use Modules\Promotion\Enums\PromotionStatus;
use Modules\Promotion\Interfaces\Repositories\PromotionRepositoryInterface;
use Modules\Promotion\Models\StudentPromotion;
use Modules\Student\Models\StudentProfile;

/**
 * Data access only. Validation and transaction boundaries belong to the
 * Actions; nothing here decides whether a promotion is allowed.
 */
class PromotionRepository implements PromotionRepositoryInterface
{
    public function findById(int $id): ?StudentPromotion
    {
        return StudentPromotion::query()->find($id);
    }

    /**
     * @param  array<int, int>  $studentIds
     * @return Collection<int, StudentPromotion>
     */
    public function existingDecisions(array $studentIds, int $fromAcademicYearId): Collection
    {
        if ($studentIds === []) {
            return collect();
        }

        return StudentPromotion::query()
            ->whereIn('student_id', $studentIds)
            ->where('from_academic_year_id', $fromAcademicYearId)
            ->get()
            ->keyBy('student_id');
    }

    public function latestForStudent(int $studentId): ?StudentPromotion
    {
        return StudentPromotion::query()
            ->where('student_id', $studentId)
            ->orderByDesc('promoted_at')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * @param  array<int, int>  $classIds
     * @return array<int, int>
     */
    public function activeCountsByClass(array $classIds): array
    {
        if ($classIds === []) {
            return [];
        }

        /*
         * StudentProfile soft-deletes, and the default scope already hides
         * trashed rows — a withdrawn student must not hold a seat open.
         */
        return StudentProfile::query()
            ->whereIn('current_class_id', $classIds)
            ->select('current_class_id', DB::raw('COUNT(*) as total'))
            ->groupBy('current_class_id')
            ->pluck('total', 'current_class_id')
            ->map(static fn ($total): int => (int) $total)
            ->all();
    }

    public function record(
        StudentProfile $student,
        PromotionDecisionDTO $decision,
        int $fromAcademicYearId,
        ?int $toAcademicYearId,
        ?int $actorId,
    ): StudentPromotion {
        return StudentPromotion::query()->create([
            'student_id' => $student->id,
            'from_academic_year_id' => $fromAcademicYearId,

            // Only a promotion moves the student into another year.
            'to_academic_year_id' => $decision->status->requiresTargetYear() ? $toAcademicYearId : null,
            'from_class_id' => $student->current_class_id,
            'to_class_id' => $decision->status->requiresTargetClass() ? $decision->targetClassId : null,
            'status' => $decision->status,

            // Captured so a revert restores the exact prior state.
            'was_graduated' => (bool) $student->is_graduated,
            'promoted_by' => $actorId,
            'promoted_at' => now(),
            'remarks' => $decision->remarks,
        ]);
    }

    public function applyToStudent(
        StudentProfile $student,
        PromotionDecisionDTO $decision,
        ?int $toAcademicYearId,
    ): StudentProfile {
        $attributes = match ($decision->status) {
            PromotionStatus::Promoted => [
                'academic_year_id' => $toAcademicYearId,
                'current_class_id' => $decision->targetClassId,
            ],

            // Graduating ends the student's progression; no next class exists,
            // so the class is cleared rather than left pointing at last year.
            PromotionStatus::Graduated => [
                'is_graduated' => true,
                'current_class_id' => null,
            ],

            /*
             * A transferred student has left, so they stop being active. Their
             * record and history stay for reporting.
             */
            PromotionStatus::Transferred => [
                'is_active' => false,
            ],

            // Retained: the student stays exactly where they are.
            PromotionStatus::Retained => [],
        };

        if ($attributes !== []) {
            $student->update($attributes);
        }

        return $student->refresh();
    }

    public function restoreFrom(StudentPromotion $promotion): StudentProfile
    {
        $student = StudentProfile::query()->findOrFail($promotion->student_id);

        $student->update([
            'academic_year_id' => $promotion->from_academic_year_id,
            'current_class_id' => $promotion->from_class_id,
            'is_graduated' => $promotion->was_graduated,

            // A transfer deactivated the student; undoing it brings them back.
            'is_active' => $promotion->status === PromotionStatus::Transferred
                ? true
                : $student->is_active,
        ]);

        return $student->refresh();
    }

    public function delete(StudentPromotion $promotion): bool
    {
        return (bool) $promotion->delete();
    }
}
