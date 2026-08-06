<?php

namespace Modules\Examination\Repositories;

use Modules\Examination\DTOs\CreateExamSubjectDTO;
use Modules\Examination\DTOs\UpdateExamSubjectDTO;
use Modules\Examination\Models\Exam;
use Modules\Examination\Models\ExamSubject;

interface ExamSubjectRepositoryInterface
{
    public function findByIdOrFail(int $id): ExamSubject;

    public function create(Exam $exam, CreateExamSubjectDTO $dto): ExamSubject;

    public function update(ExamSubject $examSubject, UpdateExamSubjectDTO $dto): ExamSubject;

    public function delete(ExamSubject $examSubject): bool;
}
