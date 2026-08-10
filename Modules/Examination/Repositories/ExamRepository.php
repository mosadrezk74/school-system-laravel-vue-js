<?php

namespace Modules\Examination\Repositories;

use Modules\Examination\DTOs\CreateExamDTO;
use Modules\Examination\DTOs\UpdateExamDTO;
use Modules\Examination\Models\Exam;

/**
 * Data access only: no validation, no business rules, no transactions.
 * Callers (Actions) own the transaction boundary and business checks.
 */
class ExamRepository implements ExamRepositoryInterface
{
    public function findById(int $id): ?Exam
    {
        return Exam::query()->find($id);
    }

    public function findByIdOrFail(int $id): Exam
    {
        return Exam::query()->findOrFail($id);
    }

    public function create(CreateExamDTO $dto, ?int $actorId = null): Exam
    {
        return Exam::create([
            ...$dto->toArray(),
            'created_by' => $actorId,
            'updated_by' => $actorId,
        ]);
    }

    public function update(Exam $exam, UpdateExamDTO $dto, ?int $actorId = null): Exam
    {
        $exam->update([
            ...$dto->toArray(),
            'updated_by' => $actorId,
        ]);

        return $exam->refresh();
    }

    public function delete(Exam $exam): bool
    {
        return (bool) $exam->delete();
    }

    public function restore(Exam $exam): bool
    {
        return (bool) $exam->restore();
    }
}
