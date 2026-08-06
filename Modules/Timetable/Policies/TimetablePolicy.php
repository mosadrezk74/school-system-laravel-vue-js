<?php

namespace Modules\Timetable\Policies;

use App\Models\User;
use Modules\Timetable\Models\Timetable;

class TimetablePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('timetable.view');
    }

    public function view(User $user, Timetable $timetable): bool
    {
        return $user->can('timetable.view');
    }

    public function create(User $user): bool
    {
        return $user->can('timetable.create');
    }

    public function update(User $user, Timetable $timetable): bool
    {
        return $user->can('timetable.update');
    }

    public function delete(User $user, Timetable $timetable): bool
    {
        return $user->can('timetable.delete');
    }

    public function restore(User $user, Timetable $timetable): bool
    {
        return $user->can('timetable.delete');
    }
}
