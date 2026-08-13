<?php

namespace Modules\Core\Support\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * A tri-state flag: true, false, or absent meaning "all".
 *
 * The absent case is the reason this does not just cast: `is_active=''` has to
 * read as "no filter", because a cleared VSelect sends an empty value and
 * `(bool) ''` would silently become "show only inactive".
 */
class BooleanFilter extends Filter
{
    public static function make(): self
    {
        return new self;
    }

    /** @return array<int, FilterOperator> */
    public function operators(): array
    {
        return [FilterOperator::Equals];
    }

    public function type(): FilterType
    {
        return FilterType::Boolean;
    }

    /** @param array<string, mixed> $input */
    protected function resolve(array $input, string $key): ?bool
    {
        $value = $input[$key] ?? null;

        if ($this->isBlank($value)) {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }

    /**
     * @param  Builder<Model>  $query
     */
    protected function constrainColumn(
        Builder $query,
        string $column,
        FilterOperator $operator,
        mixed $value,
    ): void {
        $query->where($column, $value);
    }
}
