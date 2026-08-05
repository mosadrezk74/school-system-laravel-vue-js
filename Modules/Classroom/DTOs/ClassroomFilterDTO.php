<?php

namespace Modules\Classroom\DTOs;

use Illuminate\Http\Request;
use Modules\Core\Abstracts\BaseDTO;
use Modules\Core\Constants\Pagination;

final class ClassroomFilterDTO extends BaseDTO
{
    public function __construct(
        public ?string $search,
        public ?int $branchId,
        public ?string $roomType,
        public ?bool $isActive,
        public string $sortBy,
        public string $sortOrder,
        public int $perPage,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            search: $request->string(Pagination::SEARCH_PARAM)->toString() ?: null,
            branchId: $request->filled('branch_id') ? (int) $request->input('branch_id') : null,
            roomType: $request->string('room_type')->toString() ?: null,
            isActive: $request->has('is_active') ? filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : null,
            sortBy: $request->string(Pagination::SORT_PARAM, 'name')->toString(),
            sortOrder: $request->string(Pagination::ORDER_PARAM, Pagination::DEFAULT_ORDER)->toString(),
            perPage: $request->integer(Pagination::PER_PAGE_PARAM, Pagination::DEFAULT_PER_PAGE),
        );
    }
}
