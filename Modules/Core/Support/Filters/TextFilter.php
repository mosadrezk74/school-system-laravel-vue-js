<?php

namespace Modules\Core\Support\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Free-text matching on a string column.
 *
 * `contains` is first, and therefore the default, because that is what every
 * hand-written `LIKE %term%` in the modules did before operators existed.
 */
class TextFilter extends Filter
{
    public static function make(): self
    {
        return new self;
    }

    /** @return array<int, FilterOperator> */
    public function operators(): array
    {
        return [
            FilterOperator::Contains,
            FilterOperator::Equals,
            FilterOperator::NotEquals,
            FilterOperator::StartsWith,
            FilterOperator::EndsWith,
        ];
    }

    public function type(): FilterType
    {
        return FilterType::Text;
    }

    /** @param array<string, mixed> $input */
    protected function resolve(array $input, string $key): ?string
    {
        $value = $input[$key] ?? null;

        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
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
        // `value` is bound as a parameter in every branch; only `column` is
        // interpolated, and that comes from the filter definition, never input.
        match ($operator) {
            FilterOperator::Equals => $query->where($column, '=', $value),
            FilterOperator::NotEquals => $query->where($column, '!=', $value),
            FilterOperator::StartsWith => $query->where($column, 'LIKE', "{$value}%"),
            FilterOperator::EndsWith => $query->where($column, 'LIKE', "%{$value}"),
            default => $query->where($column, 'LIKE', "%{$value}%"),
        };
    }
}
