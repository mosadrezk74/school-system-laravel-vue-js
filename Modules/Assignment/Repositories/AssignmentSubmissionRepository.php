<?php

namespace Modules\Assignment\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Assignment\DTOs\GradeAssignmentSubmissionDTO;
use Modules\Assignment\DTOs\SubmitAssignmentDTO;
use Modules\Assignment\Models\AssignmentSubmission;

/**
 * Data access only: no validation, no business rules, no transactions.
 * Callers (Actions) own the transaction boundary and business checks.
 */
class AssignmentSubmissionRepository implements AssignmentSubmissionRepositoryInterface
{
    public function findById(int $id): ?AssignmentSubmission
    {
        return AssignmentSubmission::query()->find($id);
    }

    public function findByIdOrFail(int $id): AssignmentSubmission
    {
        return AssignmentSubmission::query()->findOrFail($id);
    }

    public function findByAssignmentAndStudent(int $assignmentId, int $studentId): ?AssignmentSubmission
    {
        return AssignmentSubmission::query()
            ->where('assignment_id', $assignmentId)
            ->where('student_id', $studentId)
            ->first();
    }

    public function create(SubmitAssignmentDTO $dto, bool $isLate): AssignmentSubmission
    {
        return AssignmentSubmission::create([
            'assignment_id' => $dto->assignmentId,
            'student_id' => $dto->studentId,
            'submitted_at' => $dto->submittedAt,
            'is_late' => $isLate,
        ]);
    }

    public function update(AssignmentSubmission $submission, GradeAssignmentSubmissionDTO $dto): AssignmentSubmission
    {
        $submission->update([
            'score' => $dto->score,
            'grade' => $dto->grade,
            'feedback' => $dto->feedback,
            'is_graded' => true,
        ]);

        return $submission->refresh();
    }

    public function listByAssignment(int $assignmentId): Collection
    {
        return AssignmentSubmission::query()
            ->where('assignment_id', $assignmentId)
            ->with('student.user')
            ->get();
    }
}
