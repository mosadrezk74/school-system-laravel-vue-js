<?php

namespace Modules\News\DTOs;

use Illuminate\Http\Request;
use Modules\Core\Constants\Pagination;

final readonly class NewsFilterDTO
{
    public function __construct(
        public ?string $search,
        public ?string $status,
        public ?string $audience,
        public string $sortBy,
        public string $sortOrder,
        public int $perPage,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            search: $request->string(Pagination::SEARCH_PARAM)->toString() ?: null,
            status: $request->string('status')->toString() ?: null,
            audience: $request->string('audience')->toString() ?: null,

            // Newest first: an admin is normally looking at what they just wrote.
            sortBy: $request->string(Pagination::SORT_PARAM, 'created_at')->toString(),
            sortOrder: $request->string(Pagination::ORDER_PARAM, 'desc')->toString(),
            perPage: $request->integer(Pagination::PER_PAGE_PARAM, Pagination::DEFAULT_PER_PAGE),
        );
    }
}
