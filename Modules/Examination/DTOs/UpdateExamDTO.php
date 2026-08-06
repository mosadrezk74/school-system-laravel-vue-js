<?php

namespace Modules\Examination\DTOs;

use Modules\Examination\Http\Requests\UpdateExamRequest;

final readonly class UpdateExamDTO
{
    public function __construct(
        public int $academicYearId,
        public ?int $semesterId,
        public ?int $classId,
        public array $name,
        public ?string $code,
        public string $examType,
        public string $startDate,
        public string $endDate,
        public float $passingGrade,
        public float $totalMarks,
        public float $weightage,
        public bool $isPublished,
    ) {}

    public static function fromRequest(UpdateExamRequest $request): self
    {
        return new self(
            academicYearId: (int) $request->validated('academic_year_id'),
            semesterId: $request->validated('semester_id') ? (int) $request->validated('semester_id') : null,
            classId: $request->validated('class_id') ? (int) $request->validated('class_id') : null,
            name: $request->validated('name'),
            code: $request->validated('code'),
            examType: $request->validated('exam_type'),
            startDate: $request->validated('start_date'),
            endDate: $request->validated('end_date'),
            passingGrade: (float) $request->validated('passing_grade', 60),
            totalMarks: (float) $request->validated('total_marks', 100),
            weightage: (float) $request->validated('weightage', 1),
            isPublished: (bool) $request->validated('is_published', false),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'academic_year_id' => $this->academicYearId,
            'semester_id' => $this->semesterId,
            'class_id' => $this->classId,
            'name' => $this->name,
            'code' => $this->code,
            'exam_type' => $this->examType,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'passing_grade' => $this->passingGrade,
            'total_marks' => $this->totalMarks,
            'weightage' => $this->weightage,
            'is_published' => $this->isPublished,
        ];
    }
}
