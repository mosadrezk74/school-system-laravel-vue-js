<?php

namespace Modules\Teacher\Policies;

use App\Models\User;
use Modules\Teacher\Models\TeacherProfile;

class TeacherPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('teacher.view');
    }

    public function view(User $user, TeacherProfile $teacher): bool
    {
        return $user->can('teacher.view');
    }

    public function create(User $user): bool
    {
        return $user->can('teacher.create');
    }

    public function update(User $user, TeacherProfile $teacher): bool
    {
        return $user->can('teacher.update');
    }

    public function delete(User $user, TeacherProfile $teacher): bool
    {
        return $user->can('teacher.delete');
    }

    public function restore(User $user, TeacherProfile $teacher): bool
    {
        return $user->can('teacher.delete');
    }
}
