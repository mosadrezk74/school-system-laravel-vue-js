<?php

namespace Modules\Core\Support\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * An inclusive `{key}_from` / `{key}_to` window over a date or datetime column.
 *
 * The bounds are compared with `whereDate`, so a `_to` of 2026-08-13 includes
 * everything recorded that day rather than cutting off at midnight — which is
 * what a user picking an end date means, and what the existing `date_from` /
 * `date_to` pairs across Finance and Attendance already imply.
 *
 * Either bound may stand alone; an open-ended window is a valid filter.
 */
class DateRangeFilter extends Filter
{
    public static function make(): self
    {
        return new self;
    }

    /** @return array<int, FilterOperator> */
    public function operators(): array
    {
        return [FilterOperator::Between];
    }

    public function type(): FilterType
    {
        return FilterType::DateRange;
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array{0: ?string, 1: ?string}|null
     */
    protected function resolve(array $input, string $key): ?array
    {
        $from = $this->date($input["{$key}_from"] ?? null);
        $to = $this->date($input["{$key}_to"] ?? null);

        if ($from === null && $to === null) {
            return null;
        }

        return [$from, $to];
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
        [$from, $to] = $value;

        if ($from !== null) {
            $query->whereDate($column, '>=', $from);
        }

        if ($to !== null) {
            $query->whereDate($column, '<=', $to);
        }
    }
}
