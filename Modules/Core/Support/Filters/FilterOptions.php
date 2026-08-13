<?php

namespace Modules\Core\Support\Filters;

use Illuminate\Database\Eloquent\Model;

/**
 * Builds the `{title, value}` option lists AppFilterBar renders selects from.
 *
 * Enums produce this shape already via BaseEnum::selectOptions(); this is the
 * equivalent for the database-driven lookups — classes, subjects, academic
 * years — that a controller loads and hands to `Query::filterSchema()`.
 */
final class FilterOptions
{
    /**
     * Reading the attribute rather than selecting the raw column matters for
     * translatable names: `name` is a {locale => text} JSON map, and passing it
     * through unresolved renders as "[object Object]" in a select.
     *
     * @param  iterable<int, Model>  $models
     * @return array<int, array{title: string, value: mixed}>
     */
    public static function fromModels(iterable $models, string $titleAttribute = 'name'): array
    {
        $options = [];

        foreach ($models as $model) {
            $options[] = [
                'title' => (string) $model->getAttribute($titleAttribute),
                'value' => $model->getKey(),
            ];
        }

        return $options;
    }

    /**
     * The same, for models whose label is not a plain attribute — a student is
     * named on the related user, an invoice by its number and the student it
     * bills.
     *
     * @param  iterable<int, Model>  $models
     * @param  callable(Model): string  $title
     * @return array<int, array{title: string, value: mixed}>
     */
    public static function from(iterable $models, callable $title): array
    {
        $options = [];

        foreach ($models as $model) {
            $options[] = ['title' => $title($model), 'value' => $model->getKey()];
        }

        return $options;
    }

    /**
     * Prepends a "not set" choice backed by the null sentinel, for filters
     * marked `nullable()` — students with no class yet, invoices with no
     * payment method.
     *
     * @param  array<int, array{title: string, value: mixed}>  $options
     * @return array<int, array{title: string, value: mixed}>
     */
    public static function withNotSet(array $options, ?string $label = null): array
    {
        return [
            ['title' => $label ?? __('core::filters.noValue'), 'value' => Filter::NULL],
            ...$options,
        ];
    }
}
