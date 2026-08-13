<?php

namespace Modules\Core\Support\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Equality on a scalar column — in practice, a foreign key chosen from a
 * dropdown.
 *
 * An array value becomes `whereIn`, so the same definition serves both a
 * single-select and a multi-select without the Query having to care.
 */
class ExactFilter extends Filter
{
    public static function make(): self
    {
        return new self;
    }

    /** @return array<int, FilterOperator> */
    public function operators(): array
    {
        return [
            FilterOperator::Equals,
            FilterOperator::NotEquals,
            FilterOperator::In,
        ];
    }

    public function type(): FilterType
    {
        return FilterType::Select;
    }

    /** @param array<string, mixed> $input */
    protected function resolve(array $input, string $key): mixed
    {
        $value = $input[$key] ?? null;

        if ($this->isBlank($value)) {
            return null;
        }

        if (is_array($value)) {
            $values = array_values(array_filter($value, fn (mixed $item): bool => ! $this->isBlank($item)));

            return $values === [] ? null : $values;
        }

        return is_scalar($value) ? $value : null;
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
        if (is_array($value)) {
            $operator === FilterOperator::NotEquals
                ? $query->whereNotIn($column, $value)
                : $query->whereIn($column, $value);

            return;
        }

        $operator === FilterOperator::NotEquals
            ? $query->where($column, '!=', $value)
            : $query->where($column, '=', $value);
    }
}
