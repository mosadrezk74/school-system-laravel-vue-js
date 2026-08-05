<?php

namespace Modules\Core\Filament\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait HasTranslatableGlobalSearch
{
    /**
     * Filament's default global search builds a LIKE against the raw column.
     * For the catalog models that column is a JSON object, so the query would
     * match locale keys and other locales' text. Search each locale explicitly,
     * mirroring how the API's own queries do it.
     */
    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return static::getEloquentQuery();
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        $attribute = static::getRecordTitleAttribute();

        return (string) ($attribute ? $record->{$attribute} : $record->getKey());
    }

    /**
     * @return array<int, string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        $attribute = static::getRecordTitleAttribute();

        if (! $attribute) {
            return [];
        }

        return in_array($attribute, static::translatableSearchAttributes(), true)
            ? [$attribute.'->ar', $attribute.'->en']
            : [$attribute];
    }

    /**
     * Title attributes stored as per-locale JSON on this resource's model.
     *
     * @return array<int, string>
     */
    protected static function translatableSearchAttributes(): array
    {
        $model = static::getModel();

        return property_exists($model, 'translatable')
            ? (new $model)->translatable
            : [];
    }
}
