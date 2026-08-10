<?php

namespace Modules\Core\DTOs;

use Illuminate\Http\Request;
use Modules\Core\Constants\Pagination;

final readonly class ErrorReportFilterDTO
{
    public function __construct(
        public ?string $search,
        public ?string $status,
        public ?string $level,
        public string $sortBy,
        public string $sortOrder,
        public int $perPage,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            search: $request->input(Pagination::SEARCH_PARAM),
            status: $request->input('status'),
            level: $request->input('level'),
            sortBy: $request->input(Pagination::SORT_PARAM, 'last_seen_at'),
            sortOrder: $request->input(Pagination::ORDER_PARAM, Pagination::DEFAULT_ORDER),
            perPage: (int) $request->input(Pagination::PER_PAGE_PARAM, Pagination::DEFAULT_PER_PAGE),
        );
    }
}
