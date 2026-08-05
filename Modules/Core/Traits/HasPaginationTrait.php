<?php

namespace Modules\Core\Traits;

use Modules\Core\Constants\Pagination;

trait HasPaginationTrait
{
    /**
     * Clamp a client-supplied per_page into a safe range so a request can't
     * ask for the whole table.
     */
    protected function resolvePerPage(mixed $perPage = null): int
    {
        $perPage = is_numeric($perPage) ? (int) $perPage : Pagination::DEFAULT_PER_PAGE;

        return max(
            Pagination::MIN_PER_PAGE,
            min($perPage, Pagination::MAX_PER_PAGE),
        );
    }
}
