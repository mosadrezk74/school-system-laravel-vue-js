<?php

namespace Modules\Attendance\DTOs;

use Illuminate\Http\Request;
use Modules\Academic\Services\AcademicContextService;

final readonly class StudentAttendanceFilterDTO
{
    public function __construct(
        public int $studentId,
        public ?int $academicYearId,
        public ?int $semesterId,
        public ?string $dateFrom,
        public ?string $dateTo,
    ) {}

    public static function fromRequest(Request $request, int $studentId): self
    {
        return new self(
            studentId: $studentId,
            academicYearId: AcademicContextService::yearId(),
            semesterId: $request->filled('semester_id') ? (int) $request->input('semester_id') : null,
            dateFrom: $request->string('date_from')->toString() ?: null,
            dateTo: $request->string('date_to')->toString() ?: null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'student_id' => $this->studentId,
            'academic_year_id' => $this->academicYearId,
            'semester_id' => $this->semesterId,
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
        ];
    }
}
