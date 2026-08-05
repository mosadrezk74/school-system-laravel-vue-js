<?php

namespace Modules\Core\Traits;

use Modules\Core\Scopes\WithoutTrashedUserScope;

trait HidesTrashedUsers
{
    public static function bootHidesTrashedUsers(): void
    {
        static::addGlobalScope(new WithoutTrashedUserScope);
    }

    public function scopeWithTrashedUsers($query)
    {
        return $query->withoutGlobalScope(WithoutTrashedUserScope::class);
    }
}
