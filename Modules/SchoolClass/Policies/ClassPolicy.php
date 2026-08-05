<?php

namespace Modules\SchoolClass\Policies;

use App\Models\User;
use Modules\SchoolClass\Models\SchoolClass;

class ClassPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('school-class.view');
    }

    public function view(User $user, SchoolClass $class): bool
    {
        return $user->can('school-class.view');
    }

    public function create(User $user): bool
    {
        return $user->can('school-class.create');
    }

    public function update(User $user, SchoolClass $class): bool
    {
        return $user->can('school-class.update');
    }

    public function delete(User $user, SchoolClass $class): bool
    {
        return $user->can('school-class.delete');
    }

    public function restore(User $user, SchoolClass $class): bool
    {
        return $user->can('school-class.delete');
    }
}
