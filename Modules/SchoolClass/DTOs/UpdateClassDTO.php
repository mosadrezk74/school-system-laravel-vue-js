<?php

namespace Modules\SchoolClass\DTOs;

use Modules\Academic\Services\AcademicYearService;
use Modules\SchoolClass\Http\Requests\UpdateClassRequest;

final readonly class UpdateClassDTO
{
    public function __construct(
        public int $gradeLevelId,
        public int $academicYearId,
        public ?int $classroomId,
        public string $name,
        public string $sectionCode,
        public int $capacity,
        public ?int $classTeacherId,
        public ?int $assistantTeacherId,
        public bool $isActive,
    ) {}

    public static function fromRequest(UpdateClassRequest $request): self
    {
        return new self(
            gradeLevelId: (int) $request->validated('grade_level_id'),
            academicYearId: AcademicYearService::requiredId(),
            classroomId: $request->validated('classroom_id') ? (int) $request->validated('classroom_id') : null,
            name: $request->validated('name'),
            sectionCode: $request->validated('section_code'),
            capacity: (int) $request->validated('capacity'),
            classTeacherId: $request->validated('class_teacher_id') ? (int) $request->validated('class_teacher_id') : null,
            assistantTeacherId: $request->validated('assistant_teacher_id') ? (int) $request->validated('assistant_teacher_id') : null,
            isActive: (bool) $request->validated('is_active', true),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'grade_level_id' => $this->gradeLevelId,
            'academic_year_id' => $this->academicYearId,
            'classroom_id' => $this->classroomId,
            'name' => $this->name,
            'section_code' => $this->sectionCode,
            'capacity' => $this->capacity,
            'class_teacher_id' => $this->classTeacherId,
            'assistant_teacher_id' => $this->assistantTeacherId,
            'is_active' => $this->isActive,
        ];
    }
}
