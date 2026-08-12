<?php

namespace Modules\Auth\Policies;

use App\Models\User;
use Modules\Auth\Enums\UserRole;

/**
 * Gate checks for the administrative-accounts screen.
 *
 * Registered for User::class, which had no policy before — the portal reaches
 * students and guardians through their profile models (StudentPolicy and
 * friends), never through a User gate, so nothing else is affected.
 */
class AdminPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('user.view');
    }

    public function view(User $user, User $admin): bool
    {
        return $user->can('user.view');
    }

    public function create(User $user): bool
    {
        return $user->can('user.create');
    }

    /** An owner's account is only editable by an owner. */
    public function update(User $user, User $admin): bool
    {
        if (! $user->can('user.update')) {
            return false;
        }

        return $admin->role !== UserRole::Owner || $user->role === UserRole::Owner;
    }

    /**
     * Self-delete is refused here as well as in the Action, so the UI can
     * hide the control and the server still enforces it.
     */
    public function delete(User $user, User $admin): bool
    {
        if (! $user->can('user.delete') || $user->getKey() === $admin->getKey()) {
            return false;
        }

        return $admin->role !== UserRole::Owner || $user->role === UserRole::Owner;
    }
}
