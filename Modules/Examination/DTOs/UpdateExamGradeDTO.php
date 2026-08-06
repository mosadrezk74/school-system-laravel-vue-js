<?php

namespace Modules\Examination\DTOs;

use Modules\Examination\Http\Requests\UpdateExamGradeRequest;

final readonly class UpdateExamGradeDTO
{
    public function __construct(
        public int $gradeLevelId,
        public ?int $subjectId,
        public string $gradeName,
        public float $gradePoint,
        public float $minPercentage,
        public float $maxPercentage,
        public bool $isPassing,
    ) {}

    public static function fromRequest(UpdateExamGradeRequest $request): self
    {
        return new self(
            gradeLevelId: (int) $request->validated('grade_level_id'),
            subjectId: $request->validated('subject_id') ? (int) $request->validated('subject_id') : null,
            gradeName: $request->validated('grade_name'),
            gradePoint: (float) $request->validated('grade_point'),
            minPercentage: (float) $request->validated('min_percentage'),
            maxPercentage: (float) $request->validated('max_percentage'),
            isPassing: (bool) $request->validated('is_passing', false),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'grade_level_id' => $this->gradeLevelId,
            'subject_id' => $this->subjectId,
            'grade_name' => $this->gradeName,
            'grade_point' => $this->gradePoint,
            'min_percentage' => $this->minPercentage,
            'max_percentage' => $this->maxPercentage,
            'is_passing' => $this->isPassing,
        ];
    }
}
