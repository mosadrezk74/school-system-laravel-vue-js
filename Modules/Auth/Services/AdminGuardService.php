<?php

namespace Modules\Auth\Services;

use App\Models\User;
use Modules\Auth\Enums\UserRole;
use Modules\Auth\Exceptions\AdminOperationException;
use Modules\Auth\Interfaces\Repositories\AdminRepositoryInterface;

/**
 * The rules that decide whether an administrative account change is safe.
 *
 * Two dangers drive everything here: privilege escalation (an admin handing
 * itself owner) and lockout (removing the last account that can still sign in
 * and administer the school). Both are enforced server-side; the Vue form's
 * role dropdown is a convenience, never the boundary.
 */
class AdminGuardService
{
    public function __construct(
        private readonly AdminRepositoryInterface $repository,
    ) {}

    public function actor(): ?User
    {
        return auth()->user();
    }

    public function actorIsOwner(): bool
    {
        return $this->actor()?->role === UserRole::Owner;
    }

    public function isSelf(User $admin): bool
    {
        return $this->actor()?->getKey() === $admin->getKey();
    }

    /**
     * Resolves the account type to store, rejecting an unauthorised attempt
     * to create or become an owner.
     */
    public function resolveAccountRole(?string $requested, string $fallback): string
    {
        $role = $requested ?: $fallback;

        if ($role === UserRole::Owner->value && ! $this->actorIsOwner()) {
            throw AdminOperationException::ownerAssignableByOwnerOnly();
        }

        if (! in_array($role, [UserRole::Owner->value, UserRole::Admin->value], true)) {
            throw AdminOperationException::ownerAssignableByOwnerOnly();
        }

        return $role;
    }

    /** An owner's account is only editable by an owner. */
    public function assertMayModify(User $admin): void
    {
        if ($admin->role === UserRole::Owner && ! $this->actorIsOwner()) {
            throw AdminOperationException::cannotModifyOwner();
        }
    }

    /**
     * Spatie roles are what actually grant abilities, so the same
     * owner-escalation guard applies to the assigned role names.
     *
     * @param  array<int, string>  $roles
     */
    public function assertMayAssignRoles(array $roles): void
    {
        if (in_array(UserRole::Owner->value, $roles, true) && ! $this->actorIsOwner()) {
            throw AdminOperationException::ownerAssignableByOwnerOnly();
        }
    }

    public function assertNotSelfDelete(User $admin): void
    {
        if ($this->isSelf($admin)) {
            throw AdminOperationException::cannotDeleteSelf();
        }
    }

    /**
     * Deleting or deactivating must leave at least one active administrator
     * behind. Counting *other* accounts means the check reads the same way
     * whether the target is currently active or not.
     */
    public function assertAdministratorsRemain(User $admin): void
    {
        if ($this->repository->otherActiveAdministratorsCount($admin->getKey()) < 1) {
            throw AdminOperationException::wouldLeaveNoAdministrator();
        }
    }

    /**
     * Self-deactivation is refused outright: an admin switching its own
     * account off is almost always a mistake, and it cannot be undone from
     * inside the session it just ended.
     */
    public function assertNotSelfDeactivation(User $admin, bool $willBeActive): void
    {
        if (! $willBeActive && $this->isSelf($admin)) {
            throw AdminOperationException::cannotDeactivateSelf();
        }
    }

    /**
     * Runs the deactivation guards only when the change actually removes an
     * active administrator, so an ordinary profile edit is unaffected.
     */
    public function assertDeactivationIsSafe(User $admin, bool $willBeActive): void
    {
        if ($willBeActive || ! $admin->isActive()) {
            return;
        }

        $this->assertNotSelfDeactivation($admin, $willBeActive);
        $this->assertAdministratorsRemain($admin);
    }
}
