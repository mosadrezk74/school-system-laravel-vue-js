<?php

namespace Modules\Attendance\DTOs;

use Modules\Academic\Services\AcademicContextService;
use Modules\Attendance\Http\Requests\AttendanceReportRequest;

final readonly class AttendanceReportFilterDTO
{
    public function __construct(
        public int $classId,
        public ?int $academicYearId,
        public ?int $semesterId,
        public ?string $dateFrom,
        public ?string $dateTo,
    ) {}

    public static function fromRequest(AttendanceReportRequest $request): self
    {
        return new self(
            classId: (int) $request->validated('class_id'),
            academicYearId: AcademicContextService::yearId(),
            semesterId: $request->validated('semester_id') ? (int) $request->validated('semester_id') : null,
            dateFrom: $request->validated('date_from') ?: null,
            dateTo: $request->validated('date_to') ?: null,
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
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
        ];
    }
}
