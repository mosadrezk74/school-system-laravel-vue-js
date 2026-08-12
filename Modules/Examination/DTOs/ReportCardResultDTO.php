<?php

namespace Modules\Examination\DTOs;

/**
 * A single exam result as it appears on a report card.
 */
final readonly class ReportCardResultDTO
{
    public function __construct(
        public int $resultId,
        public int $examId,
        public string $examName,
        public ?float $obtainedMarks,
        public ?float $maxMarks,
        public ?float $percentage,
        public ?string $grade,
        public ?bool $isPassed,
        public ?int $rank,
        public bool $isPublished,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'result_id' => $this->resultId,
            'exam_id' => $this->examId,
            'exam_name' => $this->examName,
            'obtained_marks' => $this->obtainedMarks,
            'max_marks' => $this->maxMarks,
            'percentage' => $this->percentage,
            'grade' => $this->grade,
            'is_passed' => $this->isPassed,

            // Standing within this exam + subject + class.
            'rank' => $this->rank,
            'is_published' => $this->isPublished,
        ];
    }
}
