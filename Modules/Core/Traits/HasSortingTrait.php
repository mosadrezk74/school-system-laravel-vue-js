<?php

namespace Modules\Core\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait HasSortingTrait
{
    /**
     * Sort by a client-supplied column, restricted to the consumer's declared
     * $sortableFields. An unknown column falls back to the default rather than
     * reaching the database, which keeps `?sort=` from becoming an injection
     * or information-disclosure vector.
     *
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    protected function applySorting(
        Builder $query,
        ?string $sortBy = null,
        ?string $direction = null,
    ): Builder {
        $fields = $this->resolveSortableFields();

        $column = in_array($sortBy, $fields, true) ? $sortBy : $this->defaultSortField();
        $direction = strtolower((string) $direction) === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($column, $direction);
    }

    /**
     * @return array<int, string>
     */
    protected function resolveSortableFields(): array
    {
        /** @phpstan-ignore-next-line property is declared by the consuming class */
        return $this->sortableFields ?? ['id'];
    }

    protected function defaultSortField(): string
    {
        return 'id';
    }
}
