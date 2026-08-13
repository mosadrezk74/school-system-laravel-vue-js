<?php

namespace Modules\Core\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Constants\Pagination;

trait HasSortingTrait
{
    /**
     * Sort by a client-supplied column, restricted to the consumer's declared
     * $sortableFields.
     *
     * Both halves are validated. An unknown column falls back to the default
     * rather than reaching the database, which keeps `?sort_by=` from becoming
     * an injection or information-disclosure vector. The direction is narrowed
     * to asc/desc because Eloquent throws InvalidArgumentException on anything
     * else — previously `?sort_order=x` was a 500 on every index page.
     *
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    protected function applySorting(
        Builder $query,
        ?string $sortBy = null,
        ?string $direction = null,
    ): Builder {
        $column = in_array($sortBy, $this->resolveSortableFields(), true)
            ? $sortBy
            : $this->defaultSortField();

        return $query->orderBy($column, $this->resolveDirection($direction));
    }

    /**
     * @return array<int, string>
     */
    protected function resolveSortableFields(): array
    {
        /** @phpstan-ignore-next-line property is declared by the consuming class */
        return $this->sortableFields ?? [Pagination::DEFAULT_SORT];
    }

    protected function defaultSortField(): string
    {
        /** @phpstan-ignore-next-line property is declared by the consuming class */
        return $this->defaultSortField ?? Pagination::DEFAULT_SORT;
    }

    /**
     * @return 'asc'|'desc'
     */
    protected function resolveDirection(?string $direction): string
    {
        return strtolower((string) $direction) === 'asc' ? 'asc' : 'desc';
    }
}
