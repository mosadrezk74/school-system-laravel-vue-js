<?php

namespace Modules\Examination\DTOs;

use Modules\Examination\Http\Requests\StoreExamSubjectRequest;

final readonly class CreateExamSubjectDTO
{
    public function __construct(
        public int $subjectId,
        public int $classId,
        public float $totalMarks,
        public float $passingMarks,
        public ?string $examDate,
        public ?string $examTime,
        public ?string $room,
    ) {}

    public static function fromRequest(StoreExamSubjectRequest $request): self
    {
        return new self(
            subjectId: (int) $request->validated('subject_id'),
            classId: (int) $request->validated('class_id'),
            totalMarks: (float) $request->validated('total_marks'),
            passingMarks: (float) $request->validated('passing_marks'),
            examDate: $request->validated('exam_date'),
            examTime: $request->validated('exam_time'),
            room: $request->validated('room'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'subject_id' => $this->subjectId,
            'class_id' => $this->classId,
            'total_marks' => $this->totalMarks,
            'passing_marks' => $this->passingMarks,
            'exam_date' => $this->examDate,
            'exam_time' => $this->examTime,
            'room' => $this->room,
        ];
    }
}
