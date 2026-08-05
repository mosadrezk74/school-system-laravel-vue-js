<?php

namespace Modules\Core\Filament\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait HasTranslatableSearch
{
    protected static function searchTranslatable(string $column): \Closure
    {
        return function (Builder $query, string $search) use ($column): Builder {
            return $query->where(function (Builder $query) use ($column, $search): void {
                $query->where("{$column}->ar", 'like', "%{$search}%")
                    ->orWhere("{$column}->en", 'like', "%{$search}%");
            });
        };
    }

    protected static function searchTranslatableRelation(string $relation, string $column): \Closure
    {
        return fn (Builder $query, string $search): Builder => $query
            ->whereHas($relation, function (Builder $query) use ($column, $search): void {
                $query->where("{$column}->ar", 'like', "%{$search}%")
                    ->orWhere("{$column}->en", 'like', "%{$search}%");
            });
    }

    protected static function sortTranslatable(string $column): \Closure
    {
        return fn (Builder $query, string $direction): Builder => $query
            ->orderBy("{$column}->".app()->getLocale(), $direction);
    }
}
