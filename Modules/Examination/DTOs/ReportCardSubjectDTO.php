<?php

namespace Modules\Examination\DTOs;

/**
 * One subject's line on a report card: every exam result the student has for
 * that subject in the reported academic context, plus the subject's totals.
 */
final readonly class ReportCardSubjectDTO
{
    /**
     * @param  array<int, ReportCardResultDTO>  $results
     */
    public function __construct(
        public int $subjectId,
        public string $subjectName,
        public array $results,
        public ?float $obtainedMarks,
        public ?float $maxMarks,
        public ?float $percentage,
        public ?string $grade,
        public ?bool $isPassed,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'subject_id' => $this->subjectId,
            'subject_name' => $this->subjectName,
            'results' => array_map(
                static fn (ReportCardResultDTO $result): array => $result->toArray(),
                $this->results,
            ),
            'obtained_marks' => $this->obtainedMarks,
            'max_marks' => $this->maxMarks,
            'percentage' => $this->percentage,
            'grade' => $this->grade,
            'is_passed' => $this->isPassed,

            // A subject with exams configured but no marks entered yet still
            // appears, so the family can see what is outstanding.
            'has_results' => $this->obtainedMarks !== null,
        ];
    }
}
