<?php

namespace Modules\Examination\DTOs;

use Illuminate\Http\Request;
use Modules\Academic\Services\AcademicContextService;
use Modules\Core\Constants\Pagination;

final readonly class ExamFilterDTO
{
    public function __construct(
        public ?string $search,
        public ?int $academicYearId,
        public ?int $semesterId,
        public ?int $classId,
        public ?string $examType,
        public ?bool $isPublished,
        public string $sortBy,
        public string $sortOrder,
        public int $perPage,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            search: $request->string(Pagination::SEARCH_PARAM)->toString() ?: null,
            academicYearId: AcademicContextService::yearId(),
            semesterId: $request->filled('semester_id') ? (int) $request->input('semester_id') : null,
            classId: $request->filled('class_id') ? (int) $request->input('class_id') : null,
            examType: $request->string('exam_type')->toString() ?: null,
            isPublished: $request->has('is_published') ? $request->boolean('is_published') : null,
            sortBy: $request->string(Pagination::SORT_PARAM, Pagination::DEFAULT_SORT)->toString(),
            sortOrder: $request->string(Pagination::ORDER_PARAM, Pagination::DEFAULT_ORDER)->toString(),
            perPage: $request->integer(Pagination::PER_PAGE_PARAM, Pagination::DEFAULT_PER_PAGE),
        );
    }
}
