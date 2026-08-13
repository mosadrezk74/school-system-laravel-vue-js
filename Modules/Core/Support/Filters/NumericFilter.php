<?php

namespace Modules\Core\Support\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Comparisons on a numeric column.
 *
 * Reads `{key}` for the single-value operators and `{key}_from` / `{key}_to`
 * for a range, matching the `date_from` / `date_to` spelling the project
 * already uses. A range wins when both are present.
 */
class NumericFilter extends Filter
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
            FilterOperator::GreaterThan,
            FilterOperator::GreaterThanOrEqual,
            FilterOperator::LessThan,
            FilterOperator::LessThanOrEqual,
            FilterOperator::Between,
        ];
    }

    public function type(): FilterType
    {
        return FilterType::NumberRange;
    }

    /**
     * @param  array<string, mixed>  $input
     * @return float|array{0: ?float, 1: ?float}|null
     */
    protected function resolve(array $input, string $key): float|array|null
    {
        $from = $this->numeric($input["{$key}_from"] ?? null);
        $to = $this->numeric($input["{$key}_to"] ?? null);

        if ($from !== null || $to !== null) {
            return [$from, $to];
        }

        return $this->numeric($input[$key] ?? null);
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
            [$from, $to] = $value;

            // An open-ended range is still a valid filter: "from 100" with no
            // upper bound is a `>=`, not an error.
            if ($from !== null) {
                $query->where($column, '>=', $from);
            }

            if ($to !== null) {
                $query->where($column, '<=', $to);
            }

            return;
        }

        match ($operator) {
            FilterOperator::NotEquals => $query->where($column, '!=', $value),
            FilterOperator::GreaterThan => $query->where($column, '>', $value),
            FilterOperator::GreaterThanOrEqual => $query->where($column, '>=', $value),
            FilterOperator::LessThan => $query->where($column, '<', $value),
            FilterOperator::LessThanOrEqual => $query->where($column, '<=', $value),
            default => $query->where($column, '=', $value),
        };
    }

    protected function numeric(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }
}
