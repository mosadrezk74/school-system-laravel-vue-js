<?php

namespace Modules\Auth\Models;

use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Permission as SpatiePermission;

/**
 * Extends Spatie's Permission, adding the schema's `module` column so the
 * permissions screen can group by module rather than listing 200 flat rows.
 */
class Permission extends SpatiePermission
{
    protected $table = 'permissions';

    /**
     * @param  Builder<Permission>  $query
     */
    public function scopeForModule(Builder $query, string $module): void
    {
        $query->where('module', $module);
    }
}
