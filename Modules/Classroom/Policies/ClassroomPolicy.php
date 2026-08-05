<?php

namespace Modules\Classroom\Policies;

use App\Models\User;
use Modules\Classroom\Models\Classroom;

class ClassroomPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('classroom.view');
    }

    public function view(User $user, Classroom $classroom): bool
    {
        return $user->can('classroom.view');
    }

    public function create(User $user): bool
    {
        return $user->can('classroom.create');
    }

    public function update(User $user, Classroom $classroom): bool
    {
        return $user->can('classroom.update');
    }

    public function delete(User $user, Classroom $classroom): bool
    {
        return $user->can('classroom.delete');
    }

    public function restore(User $user, Classroom $classroom): bool
    {
        return $user->can('classroom.delete');
    }
}
