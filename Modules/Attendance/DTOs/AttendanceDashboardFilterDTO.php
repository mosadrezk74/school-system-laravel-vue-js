<?php

namespace Modules\Attendance\DTOs;

use Illuminate\Http\Request;
use Modules\Academic\Services\AcademicContextService;

final readonly class AttendanceDashboardFilterDTO
{
    public function __construct(
        public ?int $classId,
        public ?int $academicYearId,
        public ?int $semesterId,
        public int $trendDays,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            classId: $request->filled('class_id') ? (int) $request->input('class_id') : null,
            academicYearId: AcademicContextService::yearId(),
            semesterId: $request->filled('semester_id') ? (int) $request->input('semester_id') : null,
            trendDays: 30,
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
        ];
    }
}
