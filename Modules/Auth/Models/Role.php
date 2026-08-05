<?php

namespace Modules\Auth\Models;

use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * Extends Spatie's Role so the package's pivot handling still applies, while
 * adding the schema's `is_system` flag.
 *
 * System roles ship with the product (owner, admin, teacher…). They may be
 * granted extra permissions but must not be renamed or deleted, or the
 * seeders and policies that reference them by name would break.
 */
class Role extends SpatieRole
{
    protected $table = 'roles';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
        ];
    }

    public function isSystem(): bool
    {
        return (bool) $this->is_system;
    }

    /**
     * @param  Builder<Role>  $query
     */
    public function scopeCustom(Builder $query): void
    {
        $query->where('is_system', false);
    }
}
