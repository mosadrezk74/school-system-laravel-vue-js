<?php

namespace Modules\Core\Support\Filters;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * One filterable field: the column it constrains, the operators it accepts,
 * and how flat request input becomes a constraint on the builder.
 *
 * A filter is inert until a Query names it in `allowedFilters()`. No column,
 * relation or operator is ever read from request input — the request supplies
 * values and may *choose among* declared operators, nothing more. That is what
 * keeps `?whatever=` from reaching SQL.
 */
abstract class Filter
{
    /** Value that asks for "column IS NULL", on filters marked `nullable()`. */
    public const NULL = '__null__';

    /** Value that asks for "column IS NOT NULL". */
    public const NOT_NULL = '__not_null__';

    /** Column this filter constrains; defaults to the filter's request key. */
    protected ?string $column = null;

    /** Relation the column lives on, when it is not on the queried table. */
    protected ?string $relation = null;

    /** Translation key AppFilterBar renders as this control's label. */
    protected ?string $labelKey = null;

    /** Whether the NULL / NOT_NULL sentinels are accepted for this field. */
    protected bool $nullable = false;

    /**
     * Whether the control offers an "Any" choice.
     *
     * False for fields the controller always supplies a default for — the
     * academic year being the one that matters: the list is scoped to the
     * current year unless the user picks another, so an empty choice would
     * silently fall back rather than widening to all years.
     */
    protected bool $clearable = true;

    public function column(string $column): static
    {
        $this->column = $column;

        return $this;
    }

    /**
     * Constrain through a relation instead of the queried table, e.g. a
     * student's gender which lives on `users`.
     */
    public function on(string $relation, ?string $column = null): static
    {
        $this->relation = $relation;

        if ($column !== null) {
            $this->column = $column;
        }

        return $this;
    }

    public function label(string $labelKey): static
    {
        $this->labelKey = $labelKey;

        return $this;
    }

    public function nullable(bool $nullable = true): static
    {
        $this->nullable = $nullable;

        return $this;
    }

    /** Drops the "Any" choice; the field always carries a value. */
    public function required(): static
    {
        $this->clearable = false;

        return $this;
    }

    /**
     * Operators this filter accepts. The first is the default, so a request
     * that omits `{key}_op` keeps the behaviour the field had before operators
     * existed.
     *
     * @return array<int, FilterOperator>
     */
    abstract public function operators(): array;

    abstract public function type(): FilterType;

    /**
     * Read the filter's values out of flat request input and constrain the
     * builder.
     *
     * @param  Builder<Model>  $query
     * @param  array<string, mixed>  $input
     */
    public function applyTo(Builder $query, array $input, string $key): void
    {
        $column = $this->column ?? $key;

        if ($this->nullable && $this->isNullSentinel($input[$key] ?? null)) {
            $wantsNull = $input[$key] === self::NULL;

            $this->constrain($query, function (Builder $builder) use ($column, $wantsNull): void {
                $wantsNull ? $builder->whereNull($column) : $builder->whereNotNull($column);
            });

            return;
        }

        $value = $this->resolve($input, $key);

        // Absent input is a no-op: a missing key means "not filtering", and
        // must never be read as "match null" or "match empty string".
        if ($value === null) {
            return;
        }

        $operator = $this->resolveOperator($input, $key);

        $this->constrain($query, function (Builder $builder) use ($column, $operator, $value): void {
            $this->constrainColumn($builder, $column, $operator, $value);
        });
    }

    /**
     * The metadata AppFilterBar needs to render this field.
     *
     * @return array<string, mixed>
     */
    public function definition(string $key): array
    {
        return array_filter([
            'key' => $key,
            'type' => $this->type()->value,
            'label' => $this->labelKey,
            'operators' => array_map(
                static fn (FilterOperator $operator): string => $operator->value,
                $this->operators(),
            ),
            'nullable' => $this->nullable,
            'clearable' => $this->clearable,
            'options' => $this->options(),
        ], static fn (mixed $value): bool => $value !== null);
    }

    /**
     * Fixed option list for select-style filters. Dynamic lists (classes,
     * subjects) are merged in by the Query from data the controller loads.
     *
     * @return array<int, array{title: string, value: mixed}>|null
     */
    protected function options(): ?array
    {
        return null;
    }

    /**
     * Normalise raw input into the value `constrainColumn()` expects, or null
     * when this field is not being filtered on.
     *
     * @param  array<string, mixed>  $input
     */
    abstract protected function resolve(array $input, string $key): mixed;

    /**
     * @param  Builder<Model>  $query
     */
    abstract protected function constrainColumn(
        Builder $query,
        string $column,
        FilterOperator $operator,
        mixed $value,
    ): void;

    /**
     * Run a constraint against the queried table, or inside the declared
     * relation when the column lives elsewhere.
     *
     * @param  Builder<Model>  $query
     * @param  Closure(Builder<Model>): void  $constraint
     */
    protected function constrain(Builder $query, Closure $constraint): void
    {
        if ($this->relation === null) {
            $constraint($query);

            return;
        }

        $query->whereHas($this->relation, $constraint);
    }

    /**
     * An operator named by the request is honoured only if this filter
     * declared it; anything else silently falls back to the default rather
     * than erroring, so a stale bookmark still returns a sensible list.
     *
     * @param  array<string, mixed>  $input
     */
    protected function resolveOperator(array $input, string $key): FilterOperator
    {
        $allowed = $this->operators();
        $requested = FilterOperator::tryFrom((string) ($input["{$key}_op"] ?? ''));

        return in_array($requested, $allowed, true) ? $requested : $allowed[0];
    }

    protected function isNullSentinel(mixed $value): bool
    {
        return is_string($value) && in_array($value, [self::NULL, self::NOT_NULL], true);
    }

    /** Treats '' and null alike: both mean the control was cleared. */
    protected function isBlank(mixed $value): bool
    {
        return $value === null || $value === '' || $value === [];
    }

    /**
     * Normalise a date bound to `Y-m-d`, or null if it is not one.
     *
     * A malformed bound is dropped rather than thrown, so a hand-edited URL
     * narrows nothing instead of returning a 500.
     */
    protected function date(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $parsed = date_create_immutable(trim($value));

        return $parsed === false ? null : $parsed->format('Y-m-d');
    }
}
