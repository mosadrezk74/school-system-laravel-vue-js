<?php

namespace Modules\Assignment\DTOs;

use Modules\Assignment\Http\Requests\GradeAssignmentSubmissionRequest;

final readonly class GradeAssignmentSubmissionDTO
{
    public function __construct(
        public float $score,
        public ?string $grade,
        public ?string $feedback,
    ) {}

    public static function fromRequest(GradeAssignmentSubmissionRequest $request): self
    {
        return new self(
            score: (float) $request->validated('score'),
            grade: $request->validated('grade'),
            feedback: $request->validated('feedback'),
        );
    }
}
