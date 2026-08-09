<?php

namespace Modules\Academic\Policies;

use App\Models\User;
use Modules\Academic\Models\GradeLevel;

class GradeLevelPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('grade-level.view');
    }

    public function view(User $user, GradeLevel $gradeLevel): bool
    {
        return $user->can('grade-level.view');
    }

    public function create(User $user): bool
    {
        return $user->can('grade-level.create');
    }

    public function update(User $user, GradeLevel $gradeLevel): bool
    {
        return $user->can('grade-level.update');
    }

    public function delete(User $user, GradeLevel $gradeLevel): bool
    {
        return $user->can('grade-level.delete');
    }

    public function restore(User $user, GradeLevel $gradeLevel): bool
    {
        return $user->can('grade-level.delete');
    }
}
