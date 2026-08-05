<?php

namespace Modules\Assignment\DTOs;

use Modules\Assignment\Http\Requests\UpdateStudyMaterialRequest;

final readonly class UpdateStudyMaterialDTO
{
    public function __construct(
        public ?int $classId,
        public int $subjectId,
        public int $teacherId,
        public int $academicYearId,
        public ?int $semesterId,
        public string $title,
        public ?string $description,
        public ?string $materialType,
        public bool $isPublished,
    ) {}

    public static function fromRequest(UpdateStudyMaterialRequest $request): self
    {
        return new self(
            classId: $request->validated('class_id') ? (int) $request->validated('class_id') : null,
            subjectId: (int) $request->validated('subject_id'),
            teacherId: (int) $request->validated('teacher_id'),
            academicYearId: (int) $request->validated('academic_year_id'),
            semesterId: $request->validated('semester_id') ? (int) $request->validated('semester_id') : null,
            title: $request->validated('title'),
            description: $request->validated('description'),
            materialType: $request->validated('material_type'),
            isPublished: (bool) $request->validated('is_published', true),
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
            'material_type' => $this->materialType,
            'is_published' => $this->isPublished,
        ];
    }
}
