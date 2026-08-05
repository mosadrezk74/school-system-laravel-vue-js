<?php

namespace Modules\Core\Filament\Concerns;

use BackedEnum;

trait HasEnumOptions
{
    /**
     * Localized value => label pairs for a backed enum.
     *
     * Enums exposing their own label() (the Wallet enums) are the API's own
     * wording and win. Everything else resolves against a translation prefix
     * so filter options read the same as the column beside them.
     *
     * @param  class-string<BackedEnum>  $enum
     * @return array<string, string>
     */
    protected static function enumOptions(string $enum, ?string $translationPrefix = null): array
    {
        $options = [];

        foreach ($enum::cases() as $case) {
            $options[$case->value] = match (true) {
                method_exists($case, 'label') => $case->label(),
                $translationPrefix !== null => __($translationPrefix.'.'.$case->value),
                default => $case->name,
            };
        }

        return $options;
    }
}
