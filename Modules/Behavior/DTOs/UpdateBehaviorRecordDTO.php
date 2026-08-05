<?php

namespace Modules\Behavior\DTOs;

use Modules\Behavior\Http\Requests\UpdateBehaviorRecordRequest;

final readonly class UpdateBehaviorRecordDTO
{
    public function __construct(
        public int $studentId,
        public int $behaviorCategoryId,
        public string $date,
        public ?string $description,
        public string $status,
        public ?int $reviewedBy,
    ) {}

    public static function fromRequest(UpdateBehaviorRecordRequest $request): self
    {
        return new self(
            studentId: (int) $request->validated('student_id'),
            behaviorCategoryId: (int) $request->validated('behavior_category_id'),
            date: $request->validated('date'),
            description: $request->validated('description'),
            status: $request->validated('status'),
            reviewedBy: $request->validated('reviewed_by') ? (int) $request->validated('reviewed_by') : null,
        );
    }
}
