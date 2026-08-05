<?php

namespace Modules\Student\Policies;

use App\Models\User;
use Modules\Student\Models\StudentProfile;

class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('student.view');
    }

    public function view(User $user, StudentProfile $student): bool
    {
        return $user->can('student.view');
    }

    public function create(User $user): bool
    {
        return $user->can('student.create');
    }

    public function update(User $user, StudentProfile $student): bool
    {
        return $user->can('student.update');
    }

    public function delete(User $user, StudentProfile $student): bool
    {
        return $user->can('student.delete');
    }

    public function restore(User $user, StudentProfile $student): bool
    {
        return $user->can('student.delete');
    }
}
