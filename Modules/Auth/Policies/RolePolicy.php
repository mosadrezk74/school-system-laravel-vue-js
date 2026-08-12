<?php

namespace Modules\Auth\Policies;

use App\Models\User;
use Modules\Auth\Enums\UserRole;
use Modules\Auth\Models\Role;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('role.view');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->can('role.view');
    }

    public function create(User $user): bool
    {
        return $user->can('role.create');
    }

    /**
     * The owner role is not editable through this screen by anyone but the
     * owner — it holds its power through Gate::before, so its pivot rows are
     * meaningless and editing them would only mislead.
     */
    public function update(User $user, Role $role): bool
    {
        if (! $user->can('role.update')) {
            return false;
        }

        return ! $this->isOwnerRole($role) || $user->role === UserRole::Owner;
    }

    /** System roles ship with the product and are never deletable. */
    public function delete(User $user, Role $role): bool
    {
        return $user->can('role.delete') && ! $role->isSystem();
    }

    private function isOwnerRole(Role $role): bool
    {
        return $role->name === UserRole::Owner->value;
    }
}
