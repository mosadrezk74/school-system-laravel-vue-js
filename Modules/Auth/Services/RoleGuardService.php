<?php

namespace Modules\Auth\Services;

use App\Models\User;
use Modules\Auth\Enums\UserRole;
use Modules\Auth\Exceptions\RoleOperationException;
use Modules\Auth\Models\Permission;
use Modules\Auth\Models\Role;

/**
 * The single authority on what a signed-in user may do to a role.
 *
 * Policies answer "may this user manage roles at all?"; this answers the
 * sharper question "may this user perform *this* change to *that* role?",
 * which depends on the target role and the acting user's own grants. Keeping
 * it here means the Actions stay thin and every rule is tested once.
 */
class RoleGuardService
{
    /**
     * Permissions that would end role management for everyone but the owner
     * if the admin role lost them. `role.view` is included because the
     * screen is unreachable without it.
     *
     * @var array<int, string>
     */
    private const LOCKOUT_CRITICAL = ['role.view', 'role.update'];

    public function actor(): ?User
    {
        return auth()->user();
    }

    public function actorIsOwner(): bool
    {
        return $this->actor()?->role === UserRole::Owner;
    }

    /**
     * The owner role is granted nothing in the pivot — it passes checks via
     * Gate::before — so it must never be edited through this screen.
     */
    public function assertNotOwnerRole(Role $role): void
    {
        if ($this->isOwnerRole($role) && ! $this->actorIsOwner()) {
            throw RoleOperationException::ownerRoleIsOwnerManaged();
        }
    }

    public function isOwnerRole(Role $role): bool
    {
        return $role->name === UserRole::Owner->value;
    }

    public function assertRenameAllowed(Role $role, ?string $newName): void
    {
        if ($newName === null || $newName === $role->name) {
            return;
        }

        if ($role->isSystem()) {
            throw RoleOperationException::systemRoleCannotBeRenamed();
        }
    }

    public function assertDeletable(Role $role, int $assignedUsers): void
    {
        if ($role->isSystem()) {
            throw RoleOperationException::systemRoleCannotBeDeleted();
        }

        if ($assignedUsers > 0) {
            throw RoleOperationException::roleStillAssigned();
        }
    }

    /**
     * Privilege-escalation guard: a non-owner may only hand out permissions
     * it already holds itself. Without this an admin could mint a role with
     * abilities beyond its own and then assign that role to itself.
     *
     * @param  array<int, string>  $permissions
     */
    public function assertMayGrant(array $permissions): void
    {
        if ($this->actorIsOwner()) {
            return;
        }

        $actor = $this->actor();

        if (! $actor instanceof User) {
            throw RoleOperationException::cannotGrantUnheldPermissions();
        }

        foreach ($permissions as $permission) {
            if (! $actor->can($permission)) {
                throw RoleOperationException::cannotGrantUnheldPermissions();
            }
        }
    }

    /**
     * Editing the role you are standing on must not remove the grants that
     * let you edit roles at all. The owner is exempt: Gate::before keeps it
     * in regardless of the pivot.
     *
     * @param  array<int, string>  $permissions
     */
    public function assertNoSelfLockout(Role $role, array $permissions): void
    {
        if ($this->actorIsOwner()) {
            return;
        }

        $actor = $this->actor();

        if (! $actor instanceof User || ! $actor->hasRole($role->name)) {
            return;
        }

        foreach (self::LOCKOUT_CRITICAL as $critical) {
            if (! in_array($critical, $permissions, true)) {
                throw RoleOperationException::wouldCauseLockout();
            }
        }
    }

    /**
     * Rejects names that do not exist so a typo silently becomes a no-op
     * permission rather than a stored row Spatie will never match.
     *
     * @param  array<int, string>  $permissions
     * @return array<int, string>
     */
    public function resolveExistingPermissions(array $permissions, string $guard): array
    {
        if ($permissions === []) {
            return [];
        }

        return Permission::query()
            ->where('guard_name', $guard)
            ->whereIn('name', $permissions)
            ->pluck('name')
            ->all();
    }
}
