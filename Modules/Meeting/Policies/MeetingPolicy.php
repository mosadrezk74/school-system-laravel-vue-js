<?php

namespace Modules\Meeting\Policies;

use App\Models\User;
use Modules\Auth\Enums\UserRole;
use Modules\Meeting\Models\Meeting;
use Modules\Timetable\Models\TimetableEntry;

class MeetingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('meeting.view');
    }

    public function view(User $user, Meeting $meeting): bool
    {
        return $user->can('meeting.view');
    }

    public function create(User $user): bool
    {
        return $user->can('meeting.create');
    }

    public function update(User $user, Meeting $meeting): bool
    {
        return $user->can('meeting.update') && $this->ownsOrManages($user, $meeting);
    }

    public function delete(User $user, Meeting $meeting): bool
    {
        return $user->can('meeting.delete') && $this->ownsOrManages($user, $meeting);
    }

    public function restore(User $user, Meeting $meeting): bool
    {
        return $user->can('meeting.delete') && $this->ownsOrManages($user, $meeting);
    }

    public function start(User $user, Meeting $meeting): bool
    {
        return $user->can('meeting.update') && $this->ownsOrManages($user, $meeting);
    }

    public function finish(User $user, Meeting $meeting): bool
    {
        return $user->can('meeting.update') && $this->ownsOrManages($user, $meeting);
    }

    public function cancel(User $user, Meeting $meeting): bool
    {
        return $user->can('meeting.update') && $this->ownsOrManages($user, $meeting);
    }

    public function join(User $user, Meeting $meeting): bool
    {
        return $user->can('meeting.view') && $meeting->status === 'live';
    }

    public function startForEntry(User $user, TimetableEntry $entry): bool
    {
        return $user->can('meeting.create') && ($user->role === UserRole::Admin || $entry->teacher_id === $user->id);
    }

    /**
     * Admin manages every meeting; a teacher only their own. Owner never
     * reaches this check — Gate::before grants it everything first.
     */
    private function ownsOrManages(User $user, Meeting $meeting): bool
    {
        return $user->role === UserRole::Admin || $meeting->teacher_id === $user->id;
    }
}
