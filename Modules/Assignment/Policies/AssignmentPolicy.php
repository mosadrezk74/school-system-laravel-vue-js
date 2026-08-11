<?php

namespace Modules\Assignment\Policies;

use App\Models\User;
use Modules\Assignment\Models\Assignment;
use Modules\Portal\Traits\ChecksPortalOwnership;

class AssignmentPolicy
{
    use ChecksPortalOwnership;

    public function viewAny(User $user): bool
    {
        return $user->can('assignment.view');
    }

    public function view(User $user, Assignment $assignment): bool
    {
        return $user->can('assignment.view') && $this->ownsClassOrSchoolWide($assignment->class_id);
    }

    public function create(User $user): bool
    {
        return $user->can('assignment.create');
    }

    public function update(User $user, Assignment $assignment): bool
    {
        return $user->can('assignment.update');
    }

    public function delete(User $user, Assignment $assignment): bool
    {
        return $user->can('assignment.delete');
    }

    public function restore(User $user, Assignment $assignment): bool
    {
        return $user->can('assignment.delete');
    }
}
