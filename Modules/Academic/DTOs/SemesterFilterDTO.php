<?php

namespace Modules\Academic\DTOs;

use Illuminate\Http\Request;
use Modules\Core\Constants\Pagination;

final readonly class SemesterFilterDTO
{
    public function __construct(
        public ?string $search,
        public ?int $academicYearId,
        public ?bool $isActive,
        public string $sortBy,
        public string $sortOrder,
        public int $perPage,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            search: $request->string(Pagination::SEARCH_PARAM)->toString() ?: null,
            academicYearId: $request->integer('academic_year_id') ?: null,
            isActive: $request->has('is_active')
                ? filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
                : null,
            sortBy: $request->string(Pagination::SORT_PARAM, 'sequence')->toString(),
            sortOrder: $request->string(Pagination::ORDER_PARAM, 'asc')->toString(),
            perPage: $request->integer(Pagination::PER_PAGE_PARAM, Pagination::DEFAULT_PER_PAGE),
        );
    }
}
