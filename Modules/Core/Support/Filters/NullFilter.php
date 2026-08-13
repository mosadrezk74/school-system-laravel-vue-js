<?php

namespace Modules\Core\Support\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * "Has a value" / "has no value" on a nullable column, expressed to the user
 * as a yes/no rather than as a sentinel.
 *
 * This exists for the handful of places where the absence itself is the thing
 * being looked for — students not yet assigned to a class, for instance. It is
 * deliberately opt-in per field: exposing it on every nullable column would
 * bury the useful filters under noise.
 */
class NullFilter extends Filter
{
    public static function make(): self
    {
        return new self;
    }

    /** @return array<int, FilterOperator> */
    public function operators(): array
    {
        return [FilterOperator::IsNotNull, FilterOperator::IsNull];
    }

    public function type(): FilterType
    {
        return FilterType::Boolean;
    }

    /**
     * True means "has a value". Reading it this way keeps the control labelled
     * positively ("Assigned to a class: yes"), which is how the domain talks
     * about it.
     *
     * @param  array<string, mixed>  $input
     */
    protected function resolve(array $input, string $key): ?bool
    {
        $value = $input[$key] ?? null;

        if ($this->isBlank($value)) {
            return null;
        }

        if ($value === self::NULL) {
            return false;
        }

        if ($value === self::NOT_NULL) {
            return true;
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
        $value ? $query->whereNotNull($column) : $query->whereNull($column);
    }
}
