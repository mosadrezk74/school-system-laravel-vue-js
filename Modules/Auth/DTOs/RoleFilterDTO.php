<?php

namespace Modules\Auth\DTOs;

use Illuminate\Http\Request;
use Modules\Core\Constants\Pagination;

final readonly class RoleFilterDTO
{
    public function __construct(
        public ?string $search,
        public ?bool $isSystem,
        public string $sortBy,
        public string $sortOrder,
        public int $perPage,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            search: $request->string(Pagination::SEARCH_PARAM)->toString() ?: null,
            isSystem: $request->has('is_system')
                ? filter_var($request->input('is_system'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
                : null,
            sortBy: $request->string(Pagination::SORT_PARAM, 'name')->toString(),
            sortOrder: $request->string(Pagination::ORDER_PARAM, 'asc')->toString(),
            perPage: $request->integer(Pagination::PER_PAGE_PARAM, Pagination::DEFAULT_PER_PAGE),
        );
    }
}
