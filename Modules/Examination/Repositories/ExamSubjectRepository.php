<?php

namespace Modules\Examination\Repositories;

use Modules\Examination\DTOs\CreateExamSubjectDTO;
use Modules\Examination\DTOs\UpdateExamSubjectDTO;
use Modules\Examination\Models\Exam;
use Modules\Examination\Models\ExamSubject;

/**
 * Data access only: no validation, no business rules, no transactions.
 * Callers (Actions) own the transaction boundary and business checks.
 */
class ExamSubjectRepository implements ExamSubjectRepositoryInterface
{
    public function findByIdOrFail(int $id): ExamSubject
    {
        return ExamSubject::query()->findOrFail($id);
    }

    public function create(Exam $exam, CreateExamSubjectDTO $dto): ExamSubject
    {
        return $exam->examSubjects()->create($dto->toArray());
    }

    public function update(ExamSubject $examSubject, UpdateExamSubjectDTO $dto): ExamSubject
    {
        $examSubject->update($dto->toArray());

        return $examSubject->refresh();
    }

    public function delete(ExamSubject $examSubject): bool
    {
        return (bool) $examSubject->delete();
    }
}
