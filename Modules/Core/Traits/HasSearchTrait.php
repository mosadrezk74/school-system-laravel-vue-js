<?php

namespace Modules\Core\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasSearchTrait
{
    /**
     * Apply a LIKE search across the consumer's declared $searchableFields.
     *
     * Only fields on that allow-list are ever queried, so a caller cannot
     * probe arbitrary columns.
     *
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @return Builder<\Illuminate\Database\Eloquent\Model>
     */
    protected function applySearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);
        $fields = $this->resolveSearchableFields();

        if ($term === '' || $fields === []) {
            return $query;
        }

        return $query->where(function (Builder $builder) use ($fields, $term): void {
            foreach ($fields as $field) {
                $builder->orWhere($field, 'like', "%{$term}%");
            }
        });
    }

    /**
     * @return array<int, string>
     */
    protected function resolveSearchableFields(): array
    {
        /** @phpstan-ignore-next-line property is declared by the consuming class */
        return $this->searchableFields ?? [];
    }
}
