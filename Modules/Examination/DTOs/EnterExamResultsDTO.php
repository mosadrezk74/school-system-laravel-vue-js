<?php

namespace Modules\Examination\DTOs;

use Modules\Examination\Http\Requests\StoreExamResultsRequest;

final readonly class EnterExamResultsDTO
{
    /**
     * @param  array<int, array{student_id: int, obtained_marks: ?float, remarks: ?string}>  $results
     */
    public function __construct(
        public int $examId,
        public int $subjectId,
        public int $classId,
        public array $results,
    ) {}

    public static function fromRequest(StoreExamResultsRequest $request): self
    {
        return new self(
            examId: (int) $request->validated('exam_id'),
            subjectId: (int) $request->validated('subject_id'),
            classId: (int) $request->validated('class_id'),
            results: $request->validated('results'),
        );
    }
}
