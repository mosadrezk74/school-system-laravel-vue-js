<?php

namespace Modules\Setting\DTOs;

use Illuminate\Http\Request;
use Modules\Core\Constants\Pagination;

final readonly class SchoolTermFilterDTO
{
    public function __construct(
        public ?string $search,
        public ?string $status,
        public string $sortBy,
        public string $sortOrder,
        public int $perPage,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            search: $request->string(Pagination::SEARCH_PARAM)->toString() ?: null,
            status: $request->string('status')->toString() ?: null,
            // Newest version first: that is the one an admin is working on.
            sortBy: $request->string(Pagination::SORT_PARAM, 'version')->toString(),
            sortOrder: $request->string(Pagination::ORDER_PARAM, 'desc')->toString(),
            perPage: $request->integer(Pagination::PER_PAGE_PARAM, Pagination::DEFAULT_PER_PAGE),
        );
    }
}
