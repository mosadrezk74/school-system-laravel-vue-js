<?php

namespace Modules\Core\Support\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Filtering *through* a relation, in the two shapes the domain actually needs:
 *
 *   id form       — `grade_level_id=3` on students, matched on the related
 *                   table rather than on `student_profiles`
 *   presence form — `has_guardian=true`, which is `whereHas` / `whereDoesntHave`
 *
 * The relation name is fixed by the definition. Nothing lets a request name a
 * relation, so no traversal can be reached from the URL.
 */
class RelationFilter extends Filter
{
    public static function make(string $relationName, ?string $relatedColumn = null): self
    {
        return new self($relationName, $relatedColumn);
    }

    /**
     * @param  string  $relationName  Eloquent relation on the queried model.
     * @param  string|null  $relatedColumn  Column on the related table; null
     *                                      makes this a presence-only filter.
     */
    public function __construct(
        protected readonly string $relationName,
        protected readonly ?string $relatedColumn = null,
    ) {}

    /** @return array<int, FilterOperator> */
    public function operators(): array
    {
        if ($this->relatedColumn === null) {
            return [FilterOperator::Exists, FilterOperator::NotExists];
        }

        return [
            FilterOperator::Equals,
            FilterOperator::NotEquals,
            FilterOperator::In,
        ];
    }

    public function type(): FilterType
    {
        return $this->relatedColumn === null ? FilterType::Boolean : FilterType::Select;
    }

    /** @param array<string, mixed> $input */
    protected function resolve(array $input, string $key): mixed
    {
        $value = $input[$key] ?? null;

        if ($this->isBlank($value)) {
            return null;
        }

        if ($this->relatedColumn === null) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }

        if (is_array($value)) {
            $values = array_values(array_filter($value, fn (mixed $item): bool => ! $this->isBlank($item)));

            return $values === [] ? null : $values;
        }

        return is_scalar($value) ? $value : null;
    }

    /**
     * Overridden because the relation is this filter's whole subject: the
     * base class would nest it a second time.
     *
     * @param  Builder<Model>  $query
     * @param  array<string, mixed>  $input
     */
    public function applyTo(Builder $query, array $input, string $key): void
    {
        $value = $this->resolve($input, $key);

        if ($value === null) {
            return;
        }

        if ($this->relatedColumn === null) {
            $value
                ? $query->whereHas($this->relationName)
                : $query->whereDoesntHave($this->relationName);

            return;
        }

        $operator = $this->resolveOperator($input, $key);
        $column = $this->relatedColumn;

        $query->whereHas($this->relationName, function (Builder $related) use ($column, $operator, $value): void {
            $this->constrainColumn($related, $column, $operator, $value);
        });
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
