<?php

namespace Modules\Core\Support\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * A single date compared against a date or datetime column.
 *
 * Use DateRangeFilter when the user should pick a window; this is for the
 * "on this exact day" controls the project already has, such as attendance
 * taken on a given date.
 *
 * Extends Filter rather than DateRangeFilter: the two resolve different value
 * shapes — one date against a pair of bounds — so inheriting would break the
 * contract rather than share anything but the date parser, which lives on the
 * base class.
 */
class DateFilter extends Filter
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
            FilterOperator::Before,
            FilterOperator::After,
        ];
    }

    public function type(): FilterType
    {
        return FilterType::Date;
    }

    /** @param array<string, mixed> $input */
    protected function resolve(array $input, string $key): ?string
    {
        return $this->date($input[$key] ?? null);
    }

    /**
     * `whereDate` rather than `where`: on a datetime column an equality against
     * a bare `Y-m-d` would only ever match rows stored at exactly midnight.
     *
     * @param  Builder<Model>  $query
     */
    protected function constrainColumn(
        Builder $query,
        string $column,
        FilterOperator $operator,
        mixed $value,
    ): void {
        match ($operator) {
            FilterOperator::Before => $query->whereDate($column, '<', $value),
            FilterOperator::After => $query->whereDate($column, '>', $value),
            default => $query->whereDate($column, '=', $value),
        };
    }
}
