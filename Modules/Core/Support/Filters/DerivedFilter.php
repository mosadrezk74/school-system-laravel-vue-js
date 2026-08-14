<?php

namespace Modules\Core\Support\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * A filterable field that has no column behind it.
 *
 * Some things a user filters on are predicates rather than values — "unread",
 * "has attachments", "overdue". They belong in `allowedFilters()` like any
 * other field, because that is what publishes the control to AppFilterBar,
 * echoes the value back through `activeFilters()`, and keeps the key inside
 * the whitelist. What they cannot do is constrain a column, because there
 * isn't one: a plain BooleanFilter named `unread` compiles to
 * `where unread = ?` and fails with "Unknown column".
 *
 * So this one participates fully in the schema and resolves to nothing. The
 * Query that declares it writes the actual predicate in its own
 * `applyFilters()`, where the correlated subquery or existence check that the
 * predicate really needs can be expressed.
 *
 *     'unread' => DerivedFilter::boolean()->label('chat.unread'),
 *
 * This is not a second filtering mechanism — it is the same pipeline, with the
 * column step left to the Query.
 */
class DerivedFilter extends Filter
{
    public function __construct(protected readonly FilterType $filterType) {}

    public static function make(FilterType $type): self
    {
        return new self($type);
    }

    /** The common case: a yes/no predicate. */
    public static function boolean(): self
    {
        return new self(FilterType::Boolean);
    }

    /** @return array<int, FilterOperator> */
    public function operators(): array
    {
        return [FilterOperator::Equals];
    }

    public function type(): FilterType
    {
        return $this->filterType;
    }

    /**
     * Always null, which makes `applyTo()` a no-op.
     *
     * @param  array<string, mixed>  $input
     */
    protected function resolve(array $input, string $key): mixed
    {
        return null;
    }

    /**
     * Unreachable: `resolve()` never yields a value to constrain with.
     *
     * @param  Builder<Model>  $query
     */
    protected function constrainColumn(
        Builder $query,
        string $column,
        FilterOperator $operator,
        mixed $value,
    ): void {}
}
