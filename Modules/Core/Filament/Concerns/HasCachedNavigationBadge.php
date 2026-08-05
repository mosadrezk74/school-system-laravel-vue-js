<?php

namespace Modules\Core\Filament\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait HasCachedNavigationBadge
{
    /**
     * Filament renders the badge and its colour separately, so a naive
     * implementation runs the same COUNT twice on every page load.
     *
     * The result is memoised in the container rather than a static property:
     * this application runs on Octane, where statics survive between requests
     * and would serve a stale count indefinitely. Container instances are
     * flushed per request, so the cache lives exactly one request.
     */
    protected static function navigationBadgeCount(?Builder $query = null): int
    {
        $key = 'filament.navigation-badge.'.static::class;

        if (! app()->bound($key)) {
            app()->instance($key, ($query ?? static::getEloquentQuery())->count());
        }

        return app($key);
    }
}
