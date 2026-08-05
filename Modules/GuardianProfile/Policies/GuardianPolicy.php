<?php

namespace Modules\GuardianProfile\Policies;

use App\Models\User;
use Modules\GuardianProfile\Models\GuardianProfile;

class GuardianPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('guardian.view');
    }

    public function view(User $user, GuardianProfile $guardian): bool
    {
        return $user->can('guardian.view');
    }

    public function create(User $user): bool
    {
        return $user->can('guardian.create');
    }

    public function update(User $user, GuardianProfile $guardian): bool
    {
        return $user->can('guardian.update');
    }

    public function delete(User $user, GuardianProfile $guardian): bool
    {
        return $user->can('guardian.delete');
    }

    public function restore(User $user, GuardianProfile $guardian): bool
    {
        return $user->can('guardian.delete');
    }
}
