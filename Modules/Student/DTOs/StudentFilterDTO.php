<?php

namespace Modules\Student\DTOs;

use Illuminate\Http\Request;
use Modules\Academic\Services\AcademicYearService;
use Modules\Core\Constants\Pagination;

final readonly class StudentFilterDTO
{
    public function __construct(
        public ?string $search,
        public ?int $academicYearId,
        public ?int $currentClassId,
        public ?int $gradeLevelId,
        public ?bool $isActive,
        public ?string $gender,
        public string $sortBy,
        public string $sortOrder,
        public int $perPage,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            search: $request->string(Pagination::SEARCH_PARAM)->toString() ?: null,
            academicYearId: AcademicYearService::id(),
            currentClassId: $request->filled('current_class_id') ? (int) $request->input('current_class_id') : null,
            gradeLevelId: $request->filled('grade_level_id') ? (int) $request->input('grade_level_id') : null,
            isActive: $request->has('is_active') ? $request->boolean('is_active') : null,
            gender: $request->string('gender')->toString() ?: null,
            sortBy: $request->string(Pagination::SORT_PARAM, Pagination::DEFAULT_SORT)->toString(),
            sortOrder: $request->string(Pagination::ORDER_PARAM, Pagination::DEFAULT_ORDER)->toString(),
            perPage: $request->integer(Pagination::PER_PAGE_PARAM, Pagination::DEFAULT_PER_PAGE),
        );
    }
}
