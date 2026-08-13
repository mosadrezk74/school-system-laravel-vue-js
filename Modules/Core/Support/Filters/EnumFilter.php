<?php

namespace Modules\Core\Support\Filters;

use BackedEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Equality against a PHP backed enum.
 *
 * The enum is the whitelist: a value that is not one of its cases is dropped
 * before it reaches the builder, so `?status=' OR 1=1` filters on nothing
 * rather than on anything. It is also the option list, which is why no Vue
 * page needs to restate the domain.
 *
 * @template TEnum of BackedEnum
 */
class EnumFilter extends Filter
{
    /**
     * @param  class-string<TEnum>  $enum
     * @return self<TEnum>
     */
    public static function make(string $enum): self
    {
        return new self($enum);
    }

    /**
     * @param  class-string<TEnum>  $enum
     */
    public function __construct(protected readonly string $enum) {}

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

    /**
     * Value => translated label pairs in the shape VSelect reads, taken
     * straight from the enum.
     *
     * @return array<int, array{title: string, value: string|int}>
     */
    protected function options(): array
    {
        if (method_exists($this->enum, 'selectOptions')) {
            return $this->enum::selectOptions();
        }

        return array_map(
            static fn (BackedEnum $case): array => [
                'title' => method_exists($case, 'label') ? $case->label() : (string) $case->value,
                'value' => $case->value,
            ],
            $this->enum::cases(),
        );
    }

    /**
     * @param  array<string, mixed>  $input
     * @return string|int|array<int, string|int>|null
     */
    protected function resolve(array $input, string $key): string|int|array|null
    {
        $value = $input[$key] ?? null;

        if ($this->isBlank($value)) {
            return null;
        }

        if (is_array($value)) {
            $cases = array_values(array_filter(array_map(
                fn (mixed $item): string|int|null => $this->caseValue($item),
                $value,
            ), static fn (mixed $item): bool => $item !== null));

            return $cases === [] ? null : $cases;
        }

        return $this->caseValue($value);
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

    /**
     * Null for anything that is not a case of the enum.
     *
     * Matching is done against `cases()` rather than `tryFrom()` so that an
     * int-backed enum handed a non-numeric string returns null instead of
     * raising a TypeError on the coercion.
     */
    protected function caseValue(mixed $value): string|int|null
    {
        if (! is_scalar($value) || is_bool($value)) {
            return null;
        }

        foreach ($this->enum::cases() as $case) {
            if ((string) $case->value === (string) $value) {
                return $case->value;
            }
        }

        return null;
    }
}
