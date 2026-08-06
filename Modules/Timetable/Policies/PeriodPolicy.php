<?php

namespace Modules\Timetable\Policies;

use App\Models\User;
use Modules\Timetable\Models\Period;

class PeriodPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('timetable.view');
    }

    public function view(User $user, Period $period): bool
    {
        return $user->can('timetable.view');
    }

    public function create(User $user): bool
    {
        return $user->can('timetable.create');
    }

    public function update(User $user, Period $period): bool
    {
        return $user->can('timetable.update');
    }

    public function delete(User $user, Period $period): bool
    {
        return $user->can('timetable.delete');
    }

    public function restore(User $user, Period $period): bool
    {
        return $user->can('timetable.delete');
    }
}
