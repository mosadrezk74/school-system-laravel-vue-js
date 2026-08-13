<?php

namespace Modules\Core\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Support\Filters\TranslatableFilter;

/**
 * Global search across a Query's declared columns.
 *
 * Twelve Query classes had each overridden `search()` with the same body,
 * differing only in which columns they looped over — and four of those also
 * carried an identical copy of the translatable locale loop. All three shapes
 * a module actually needs are declarative here:
 *
 *   $searchableFields            plain columns on the queried table
 *   $translatableSearchFields    JSON columns keyed by locale
 *   $searchableRelations         ['user' => ['name', 'email']]
 *
 * Only declared columns are ever queried, so a search term can never probe a
 * column the Query did not open up.
 */
trait HasSearchTrait
{
    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    protected function applySearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        $columns = $this->resolveSearchableFields();
        $translatable = $this->resolveTranslatableSearchFields();
        $relations = $this->resolveSearchableRelations();

        if ($term === '' || ($columns === [] && $translatable === [] && $relations === [])) {
            return $query;
        }

        /*
         * One grouped OR. Without the group, combining search with any other
         * filter would let the OR escape and match rows the other filters
         * exclude.
         */
        return $query->where(function (Builder $builder) use ($columns, $translatable, $relations, $term): void {
            foreach ($columns as $column) {
                $builder->orWhere($column, 'LIKE', "%{$term}%");
            }

            foreach ($translatable as $column) {
                foreach (TranslatableFilter::locales() as $locale) {
                    $builder->orWhere("{$column}->{$locale}", 'LIKE', "%{$term}%");
                }
            }

            foreach ($relations as $relation => $relationColumns) {
                $builder->orWhereHas($relation, function (Builder $related) use ($relationColumns, $term): void {
                    $related->where(function (Builder $inner) use ($relationColumns, $term): void {
                        foreach ($relationColumns as $column) {
                            $inner->orWhere($column, 'LIKE', "%{$term}%");
                        }
                    });
                });
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

    /**
     * @return array<int, string>
     */
    protected function resolveTranslatableSearchFields(): array
    {
        /** @phpstan-ignore-next-line property is declared by the consuming class */
        return $this->translatableSearchFields ?? [];
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function resolveSearchableRelations(): array
    {
        /** @phpstan-ignore-next-line property is declared by the consuming class */
        return $this->searchableRelations ?? [];
    }
}
