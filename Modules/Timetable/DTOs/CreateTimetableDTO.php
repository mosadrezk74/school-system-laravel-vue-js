<?php

namespace Modules\Timetable\DTOs;

use Modules\Academic\Services\AcademicYearService;
use Modules\Timetable\Http\Requests\StoreTimetableRequest;
use Modules\Timetable\Http\Requests\UpdateTimetableRequest;

final readonly class CreateTimetableDTO
{
    public function __construct(
        public int $classId,
        public int $academicYearId,
        public ?int $semesterId,
        public bool $isActive,
    ) {}

    public static function fromRequest(StoreTimetableRequest|UpdateTimetableRequest $request): self
    {
        return new self(
            classId: (int) $request->validated('class_id'),
            academicYearId: AcademicYearService::requiredId(),
            semesterId: $request->validated('semester_id') ? (int) $request->validated('semester_id') : null,
            isActive: (bool) $request->validated('is_active', true),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'class_id' => $this->classId,
            'academic_year_id' => $this->academicYearId,
            'semester_id' => $this->semesterId,
            'is_active' => $this->isActive,
        ];
    }
}
