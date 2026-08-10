<?php

namespace Modules\Examination\Repositories;

use Modules\Examination\DTOs\CreateExamDTO;
use Modules\Examination\DTOs\UpdateExamDTO;
use Modules\Examination\Models\Exam;

interface ExamRepositoryInterface
{
    public function findById(int $id): ?Exam;

    public function findByIdOrFail(int $id): Exam;

    public function create(CreateExamDTO $dto, ?int $actorId = null): Exam;

    public function update(Exam $exam, UpdateExamDTO $dto, ?int $actorId = null): Exam;

    public function delete(Exam $exam): bool;

    public function restore(Exam $exam): bool;
}
