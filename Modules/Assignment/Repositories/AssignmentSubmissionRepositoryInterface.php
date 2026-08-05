<?php

namespace Modules\Assignment\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Assignment\DTOs\GradeAssignmentSubmissionDTO;
use Modules\Assignment\DTOs\SubmitAssignmentDTO;
use Modules\Assignment\Models\AssignmentSubmission;

interface AssignmentSubmissionRepositoryInterface
{
    public function findById(int $id): ?AssignmentSubmission;

    public function findByIdOrFail(int $id): AssignmentSubmission;

    public function findByAssignmentAndStudent(int $assignmentId, int $studentId): ?AssignmentSubmission;

    public function create(SubmitAssignmentDTO $dto, bool $isLate): AssignmentSubmission;

    public function update(AssignmentSubmission $submission, GradeAssignmentSubmissionDTO $dto): AssignmentSubmission;

    public function listByAssignment(int $assignmentId): Collection;
}
