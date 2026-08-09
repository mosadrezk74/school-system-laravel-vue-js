<?php

namespace Modules\Academic\Policies;

use App\Models\User;
use Modules\Academic\Models\Semester;

class SemesterPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('semester.view');
    }

    public function view(User $user, Semester $semester): bool
    {
        return $user->can('semester.view');
    }

    public function create(User $user): bool
    {
        return $user->can('semester.create');
    }

    public function update(User $user, Semester $semester): bool
    {
        return $user->can('semester.update');
    }

    public function delete(User $user, Semester $semester): bool
    {
        return $user->can('semester.delete');
    }

    public function restore(User $user, Semester $semester): bool
    {
        return $user->can('semester.delete');
    }
}
