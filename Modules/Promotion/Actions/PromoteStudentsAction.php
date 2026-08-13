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
            /*
             * The target year and semester are the administrator's explicit
             * choice, never the current academic context — resolved and
             * validated together so a term from one year can never be paired
             * with another year.
             */
            [$targetYearId, $targetSemesterId] = $this->resolveTarget($dto, $sourceYearId);

            $students = $this->loadStudents($dto);
            $existing = $this->repository->existingDecisions($dto->studentIds(), $sourceYearId);

            // Phase 1 — validate everything. Nothing is written yet.
            $targetClasses = $this->validateAll($dto, $students, $existing, $sourceYearId, $targetYearId);

            // Phase 2 — apply, now that the whole batch is known to be valid.
            return $this->applyAll(
                $dto,
                $students,
                $sourceYearId,
                $targetYearId,
                $targetSemesterId,
                $targetClasses,
            );
        });
    }

    /**
     * The target year and term are only required when at least one student is
     * actually being moved; a batch of retentions needs neither.
     *
     * @return array{0: int|null, 1: int|null}
     */
    private function resolveTarget(PromoteStudentsDTO $dto, int $sourceYearId): array
    {
        $movesAnyone = collect($dto->decisions)
            ->contains(fn (PromotionDecisionDTO $decision): bool => $decision->status->requiresTargetYear());

        if (! $movesAnyone) {
            return [null, null];
        }

        $targetYearId = $this->guard->assertTargetYear($dto->targetAcademicYearId, $sourceYearId)->id;

        // Existence and active state only — terms are not tied to the year.
        $targetSemesterId = $this->guard->assertTargetSemester($dto->targetSemesterId)->id;

        return [$targetYearId, $targetSemesterId];
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

        // Seats already taken *in the year being promoted into* — students
        // still sitting in the source year must not block the intake.
        $counts = $this->repository->activeCountsByClass($classIds, $targetYearId);
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

            $class = $this->guard->assertTargetClass($decision, $student);

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
        ?int $targetSemesterId,
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
                $this->repository->record(
                    $student,
                    $decision,
                    $sourceYearId,
                    $targetYearId,
                    $targetSemesterId,
                    $actorId,
                ),
            );

            $this->repository->applyToStudent($student, $decision, $targetYearId);
        }

        return $records;
    }
}
