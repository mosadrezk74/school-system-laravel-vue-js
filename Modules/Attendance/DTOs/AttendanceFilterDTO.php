<?php

namespace Modules\Attendance\DTOs;

use Illuminate\Http\Request;
use Modules\Academic\Services\AcademicContextService;
use Modules\Core\Constants\Pagination;

final readonly class AttendanceFilterDTO
{
    public function __construct(
        public ?int $classId,
        public ?int $subjectId,
        public ?int $academicYearId,
        public ?int $semesterId,
        public ?int $session,
        public ?string $dateFrom,
        public ?string $dateTo,
        public string $sortBy,
        public string $sortOrder,
        public int $perPage,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            classId: $request->filled('class_id') ? (int) $request->input('class_id') : null,
            subjectId: $request->filled('subject_id') ? (int) $request->input('subject_id') : null,
            academicYearId: AcademicContextService::yearId(),
            semesterId: $request->filled('semester_id') ? (int) $request->input('semester_id') : null,
            session: $request->filled('session') ? (int) $request->input('session') : null,
            dateFrom: $request->string('date_from')->toString() ?: null,
            dateTo: $request->string('date_to')->toString() ?: null,
            sortBy: $request->string(Pagination::SORT_PARAM, 'date')->toString(),
            sortOrder: $request->string(Pagination::ORDER_PARAM, Pagination::DEFAULT_ORDER)->toString(),
            perPage: $request->integer(Pagination::PER_PAGE_PARAM, Pagination::DEFAULT_PER_PAGE),
        );
    }
}
