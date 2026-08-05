<?php

namespace Modules\Teacher\DTOs;

use Illuminate\Http\Request;
use Modules\Core\Constants\Pagination;

final readonly class TeacherFilterDTO
{
    public function __construct(
        public ?string $search,
        public ?bool $isActive,
        public ?int $departmentId,
        public ?string $gender,
        public string $sortBy,
        public string $sortOrder,
        public int $perPage,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            search: $request->string(Pagination::SEARCH_PARAM)->toString() ?: null,
            isActive: $request->has('is_active') ? filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : null,
            departmentId: $request->filled('department_id') ? (int) $request->input('department_id') : null,
            gender: $request->string('gender')->toString() ?: null,
            sortBy: $request->string(Pagination::SORT_PARAM, 'name')->toString(),
            sortOrder: $request->string(Pagination::ORDER_PARAM, Pagination::DEFAULT_ORDER)->toString(),
            perPage: $request->integer(Pagination::PER_PAGE_PARAM, Pagination::DEFAULT_PER_PAGE),
        );
    }
}
