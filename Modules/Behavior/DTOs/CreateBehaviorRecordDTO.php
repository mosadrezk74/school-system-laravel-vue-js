<?php

namespace Modules\Behavior\DTOs;

use Modules\Behavior\Http\Requests\StoreBehaviorRecordRequest;

final readonly class CreateBehaviorRecordDTO
{
    public function __construct(
        public int $studentId,
        public int $behaviorCategoryId,
        public string $date,
        public ?string $description,
    ) {}

    public static function fromRequest(StoreBehaviorRecordRequest $request): self
    {
        return new self(
            studentId: (int) $request->validated('student_id'),
            behaviorCategoryId: (int) $request->validated('behavior_category_id'),
            date: $request->validated('date'),
            description: $request->validated('description'),
        );
    }
}
