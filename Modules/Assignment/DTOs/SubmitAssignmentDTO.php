<?php

namespace Modules\Assignment\DTOs;

use Modules\Assignment\Http\Requests\StoreAssignmentSubmissionRequest;

final readonly class SubmitAssignmentDTO
{
    public function __construct(
        public int $studentId,
        public int $assignmentId,
        public string $submittedAt,
    ) {}

    public static function fromRequest(StoreAssignmentSubmissionRequest $request, int $assignmentId): self
    {
        return new self(
            studentId: (int) $request->validated('student_id'),
            assignmentId: $assignmentId,
            submittedAt: $request->validated('submitted_at'),
        );
    }
}
