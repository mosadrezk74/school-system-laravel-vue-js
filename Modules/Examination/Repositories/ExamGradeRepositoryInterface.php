<?php

namespace Modules\Examination\Repositories;

use Modules\Examination\DTOs\CreateExamGradeDTO;
use Modules\Examination\DTOs\UpdateExamGradeDTO;
use Modules\Examination\Models\ExamGrade;

interface ExamGradeRepositoryInterface
{
    public function findById(int $id): ?ExamGrade;

    public function findByIdOrFail(int $id): ExamGrade;

    public function existsByName(int $gradeLevelId, ?int $subjectId, string $gradeName, ?int $excludeId = null): bool;

    public function create(CreateExamGradeDTO $dto): ExamGrade;

    public function update(ExamGrade $examGrade, UpdateExamGradeDTO $dto): ExamGrade;

    public function delete(ExamGrade $examGrade): bool;
}
