<?php

namespace Modules\Subject\Policies;

use App\Models\User;
use Modules\Subject\Models\Subject;

class SubjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('subject.view');
    }

    public function view(User $user, Subject $subject): bool
    {
        return $user->can('subject.view');
    }

    public function create(User $user): bool
    {
        return $user->can('subject.create');
    }

    public function update(User $user, Subject $subject): bool
    {
        return $user->can('subject.update');
    }

    public function delete(User $user, Subject $subject): bool
    {
        return $user->can('subject.delete');
    }

    public function restore(User $user, Subject $subject): bool
    {
        return $user->can('subject.delete');
    }
}
