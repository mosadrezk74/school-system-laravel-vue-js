<?php

namespace Modules\Promotion\Actions;

use Illuminate\Support\Collection;
use Modules\Academic\Services\AcademicContextService;
use Modules\Core\Abstracts\BaseAction;
use Modules\Promotion\DTOs\PromoteStudentsDTO;
use Modules\Promotion\DTOs\PromotionDecisionDTO;
use Modules\Promotion\Exceptions\PromotionOperationException;
use Modules\Promotion\Interfaces\Repositories\PromotionRepositoryInterface;
use Modules\Promotion\Models\StudentPromotion;
use Modules\Promotion\Services\PromotionGuardService;
use Modules\SchoolClass\Models\SchoolClass;
use Modules\Student\Models\StudentProfile;

/**
 * Records the end-of-year decision for a group of students.
 *
 * Validation runs over the whole batch before anything is written. A single
 * ineligible student therefore aborts the entire run, and no student is left
 * half-promoted — the guarantee the school actually needs when moving a year
 * group in one click.
 *
 * The source academic year always comes from AcademicContextService; only the
 * target year is an administrative input.
 */
class PromoteStudentsAction extends BaseAction
{
    public function __construct(
        protected PromotionRepositoryInterface $repository,
        protected PromotionGuardService $guard,
        protected AcademicContextService $context,
    ) {}

    /**
     * @param  PromoteStudentsDTO  $input
     * @return Collection<int, StudentPromotion>
     */
    public function execute(mixed $input): Collection
    {
        /** @var PromoteStudentsDTO $dto */
        $dto = $input;

        if ($dto->decisions === []) {
            throw PromotionOperationException::noStudentsSelected();
        }

        // Never taken from the request: this is the year being promoted out of.
        $sourceYearId = $this->context->requireCurrentYearId();

        return $this->executeWithTransaction(function () use ($dto, $sourceYearId): Collection {
            $targetYearId = $this->resolveTargetYear($dto, $sourceYearId);

            $students = $this->loadStudents($dto);
            $existing = $this->repository->existingDecisions($dto->studentIds(), $sourceYearId);

            // Phase 1 — validate everything. Nothing is written yet.
            $targetClasses = $this->validateAll($dto, $students, $existing, $sourceYearId, $targetYearId);

            // Phase 2 — apply, now that the whole batch is known to be valid.
            return $this->applyAll($dto, $students, $sourceYearId, $targetYearId, $targetClasses);
        });
    }

    /**
     * The target year is only required when at least one student is actually
     * being moved; a batch of retentions needs none.
     */
    private function resolveTargetYear(PromoteStudentsDTO $dto, int $sourceYearId): ?int
    {
        $movesAnyone = collect($dto->decisions)
            ->contains(fn (PromotionDecisionDTO $decision): bool => $decision->status->requiresTargetYear());

        if (! $movesAnyone) {
            return null;
        }

        return $this->guard->assertTargetYear($dto->targetAcademicYearId, $sourceYearId)->id;
    }

    /**
     * @return Collection<int, StudentProfile> Keyed by student id.
     */
    private function loadStudents(PromoteStudentsDTO $dto): Collection
    {
        $students = StudentProfile::query()
            ->with('currentClass.gradeLevel')
            ->whereIn('id', $dto->studentIds())
            ->get()
            ->keyBy('id');

        foreach ($dto->studentIds() as $studentId) {
            if (! $students->has($studentId)) {
                throw PromotionOperationException::studentNotFound();
            }
        }

        return $students;
    }

    /**
     * Validates every decision and returns the resolved target classes.
     *
     * Capacity is tracked cumulatively across the batch, so twenty students
     * sent to a class with ten seats fails here rather than overfilling it.
     *
     * @param  Collection<int, StudentProfile>  $students
     * @param  Collection<int, StudentPromotion>  $existing
     * @return array<int, SchoolClass> Keyed by student id.
     */
    private function validateAll(
        PromoteStudentsDTO $dto,
        Collection $students,
        Collection $existing,
        int $sourceYearId,
        ?int $targetYearId,
    ): array {
        $classIds = array_values(array_filter(array_map(
            static fn (PromotionDecisionDTO $d): ?int => $d->status->requiresTargetClass() ? $d->targetClassId : null,
            $dto->decisions,
        )));

        $counts = $this->repository->activeCountsByClass($classIds);
        $incoming = [];
        $resolved = [];

        foreach ($dto->decisions as $decision) {
            /** @var StudentProfile $student */
            $student = $students->get($decision->studentId);

            $this->guard->assertStudentInSourceYear($student, $sourceYearId);
            $this->guard->assertNotGraduated($student);
            $this->guard->assertNotAlreadyDecided($student, $existing);

            if (! $decision->status->requiresTargetClass()) {
                continue;
            }

            $class = $this->guard->assertTargetClass($decision, $student, (int) $targetYearId);

            $incoming[$class->id] = ($incoming[$class->id] ?? 0) + 1;
            $this->guard->assertCapacity($class, $counts, $incoming[$class->id]);

            $resolved[$decision->studentId] = $class;
        }

        return $resolved;
    }

    /**
     * @param  Collection<int, StudentProfile>  $students
     * @param  array<int, SchoolClass>  $targetClasses
     * @return Collection<int, StudentPromotion>
     */
    private function applyAll(
        PromoteStudentsDTO $dto,
        Collection $students,
        int $sourceYearId,
        ?int $targetYearId,
        array $targetClasses,
    ): Collection {
        $actorId = auth()->id();
        $records = new Collection;

        foreach ($dto->decisions as $decision) {
            /** @var StudentProfile $student */
            $student = $students->get($decision->studentId);

            // Recorded before the student is changed, so from_* captures the
            // state being left behind rather than the one just written.
            $records->push(
                $this->repository->record($student, $decision, $sourceYearId, $targetYearId, $actorId),
            );

            $this->repository->applyToStudent($student, $decision, $targetYearId);
        }

        return $records;
    }
}
