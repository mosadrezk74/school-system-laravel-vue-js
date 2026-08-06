<?php

namespace Modules\Assignment\DTOs;

use Modules\Academic\Services\AcademicYearService;
use Modules\Assignment\Http\Requests\UpdateAssignmentRequest;

final readonly class UpdateAssignmentDTO
{
    public function __construct(
        public int $classId,
        public int $subjectId,
        public int $teacherId,
        public int $academicYearId,
        public ?int $semesterId,
        public array $title,
        public ?array $description,
        public float $maxScore,
        public string $deadline,
        public ?string $assignmentType,
        public bool $isGroupWork,
        public bool $isPublished,
    ) {}

    public static function fromRequest(UpdateAssignmentRequest $request): self
    {
        return new self(
            classId: (int) $request->validated('class_id'),
            subjectId: (int) $request->validated('subject_id'),
            teacherId: (int) $request->validated('teacher_id'),
            academicYearId: AcademicYearService::requiredId(),
            semesterId: $request->validated('semester_id') ? (int) $request->validated('semester_id') : null,
            title: $request->validated('title'),
            description: $request->validated('description'),
            maxScore: (float) $request->validated('max_score'),
            deadline: $request->validated('deadline'),
            assignmentType: $request->validated('assignment_type'),
            isGroupWork: (bool) $request->validated('is_group_work', false),
            isPublished: (bool) $request->validated('is_published', false),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'class_id' => $this->classId,
            'subject_id' => $this->subjectId,
            'teacher_id' => $this->teacherId,
            'academic_year_id' => $this->academicYearId,
            'semester_id' => $this->semesterId,
            'title' => $this->title,
            'description' => $this->description,
            'max_score' => $this->maxScore,
            'deadline' => $this->deadline,
            'assignment_type' => $this->assignmentType,
            'is_group_work' => $this->isGroupWork,
            'is_published' => $this->isPublished,
        ];
    }
}
