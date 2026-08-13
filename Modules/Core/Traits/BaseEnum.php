<?php

namespace Modules\Core\Traits;

/**
 * Helpers for backed enums. PHP enums can't extend a class, so modules apply
 * this as a trait: `use BaseEnum;` inside the enum.
 */
trait BaseEnum
{
    /**
     * @return array<int, string|int>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * @return array<int, string>
     */
    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    /**
     * Value => translated label pairs, for populating a select in the SPA.
     *
     * @return array<int, array{value: string|int, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            static fn (self $case): array => [
                'value' => $case->value,
                'label' => $case->label(),
            ],
            self::cases(),
        );
    }

    /**
     * The same pairs in the shape Vuetify's VSelect reads by default, so a
     * controller can hand an enum straight to a form without the page having
     * to remap it. `options()` is kept as-is for the API resources that
     * already consume its `label` key.
     *
     * @return array<int, array{title: string, value: string|int}>
     */
    public static function selectOptions(): array
    {
        return array_map(
            static fn (self $case): array => [
                'title' => $case->label(),
                'value' => $case->value,
            ],
            self::cases(),
        );
    }

    public function label(): string
    {
        return __(str_replace('_', ' ', ucfirst((string) $this->value)));
    }
}
