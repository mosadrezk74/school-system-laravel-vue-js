<?php

namespace Modules\Core\DTOs;

use Illuminate\Http\Request;
use Modules\Core\Constants\Pagination;

/**
 * One index request: the search term, the pagination/sorting envelope, and the
 * raw filter input.
 *
 * Thirty-four per-module FilterDTOs had each restated the same four lines of
 * search/sort/per_page parsing and then enumerated their filters a second time
 * — once here and once in the Query. Filter values now travel as the flat map
 * they arrived as, because the Query's `allowedFilters()` is what decides which
 * of them are real; re-typing each one in a DTO only created a second list to
 * keep in step.
 *
 * `values` being unvalidated is safe by construction: nothing consumes a key
 * from it that the Query has not declared.
 */
final readonly class ListQueryDTO
{
    /**
     * @param  array<string, mixed>  $values  Flat filter input, whitelisted downstream.
     */
    public function __construct(
        public ?string $search,
        public array $values,
        public string $sortBy,
        public string $sortOrder,
        public int $perPage,
    ) {}

    /**
     * @param  array<string, mixed>  $defaults  Applied when the request omits
     *                                          the key — the current academic
     *                                          year being the usual case.
     */
    public static function fromRequest(Request $request, array $defaults = []): self
    {
        return new self(
            search: $request->string(Pagination::SEARCH_PARAM)->toString() ?: null,
            values: [...$defaults, ...self::filterInput($request)],
            sortBy: $request->string(Pagination::SORT_PARAM, Pagination::DEFAULT_SORT)->toString(),
            sortOrder: $request->string(Pagination::ORDER_PARAM, Pagination::DEFAULT_ORDER)->toString(),
            perPage: $request->integer(Pagination::PER_PAGE_PARAM, Pagination::DEFAULT_PER_PAGE),
        );
    }

    /**
     * Everything except the pagination envelope, with blanks dropped.
     *
     * A cleared control sends `key=`, and an empty string would otherwise read
     * as an explicit value — "" is a real filter for a text column, and would
     * override a default that should still have applied.
     *
     * @return array<string, mixed>
     */
    private static function filterInput(Request $request): array
    {
        $envelope = [
            Pagination::PAGE_PARAM,
            Pagination::PER_PAGE_PARAM,
            Pagination::SORT_PARAM,
            Pagination::ORDER_PARAM,
            Pagination::SEARCH_PARAM,
        ];

        return array_filter(
            $request->except($envelope),
            static fn (mixed $value): bool => $value !== null && $value !== '' && $value !== [],
        );
    }
}
