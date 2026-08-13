<?php

namespace Modules\Core\Support\Filters;

use Illuminate\Database\Eloquent\Builder;
use Modules\Setting\Services\LanguageService;

/**
 * Text matching on a translatable JSON column.
 *
 * The value is stored keyed by locale, so a term has to be matched against
 * each enabled language's entry. The locale list comes from LanguageService,
 * which means enabling a language in settings makes it filterable without a
 * code change — and nothing here hardcodes `ar` or `en`.
 *
 * This replaces four verbatim copies of the same locale loop that had been
 * pasted into GradeLevelQuery, SemesterQuery, ClassroomQuery and SubjectQuery.
 */
class TranslatableFilter extends Filter
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
     * One grouped OR across the active locales, so combining this with other
     * filters stays an AND of the whole group rather than leaking an OR into
     * the outer query.
     */
    protected function constrainColumn(
        Builder $query,
        string $column,
        FilterOperator $operator,
        mixed $value,
    ): void {
        $locales = static::locales();

        if ($locales === []) {
            return;
        }

        $query->where(function (Builder $builder) use ($column, $operator, $value, $locales): void {
            foreach ($locales as $locale) {
                // The locale comes from the Language settings table, not from
                // request input, so it is safe to build the JSON path with.
                $path = "{$column}->{$locale}";

                match ($operator) {
                    FilterOperator::Equals => $builder->orWhere($path, '=', $value),
                    FilterOperator::StartsWith => $builder->orWhere($path, 'LIKE', "{$value}%"),
                    FilterOperator::EndsWith => $builder->orWhere($path, 'LIKE', "%{$value}"),
                    default => $builder->orWhere($path, 'LIKE', "%{$value}%"),
                };
            }
        });
    }

    /**
     * @return array<int, string>
     */
    public static function locales(): array
    {
        return app(LanguageService::class)->activeLocales();
    }
}
