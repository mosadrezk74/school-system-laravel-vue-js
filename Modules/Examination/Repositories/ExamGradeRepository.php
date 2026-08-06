<?php

namespace Modules\Examination\Repositories;

use Modules\Examination\DTOs\CreateExamGradeDTO;
use Modules\Examination\DTOs\UpdateExamGradeDTO;
use Modules\Examination\Models\ExamGrade;

/**
 * Data access only: no validation, no business rules, no transactions.
 * Callers (Actions) own the transaction boundary and business checks.
 */
class ExamGradeRepository implements ExamGradeRepositoryInterface
{
    public function findById(int $id): ?ExamGrade
    {
        return ExamGrade::query()->find($id);
    }

    public function findByIdOrFail(int $id): ExamGrade
    {
        return ExamGrade::query()->findOrFail($id);
    }

    public function existsByName(int $gradeLevelId, ?int $subjectId, string $gradeName, ?int $excludeId = null): bool
    {
        return ExamGrade::query()
            ->where('grade_level_id', $gradeLevelId)
            ->where('subject_id', $subjectId)
            ->where('grade_name', $gradeName)
            ->when($excludeId, fn ($query) => $query->whereKeyNot($excludeId))
            ->exists();
    }

    public function create(CreateExamGradeDTO $dto): ExamGrade
    {
        return ExamGrade::create($dto->toArray());
    }

    public function update(ExamGrade $examGrade, UpdateExamGradeDTO $dto): ExamGrade
    {
        $examGrade->update($dto->toArray());

        return $examGrade->refresh();
    }

    public function delete(ExamGrade $examGrade): bool
    {
        return (bool) $examGrade->delete();
    }
}
