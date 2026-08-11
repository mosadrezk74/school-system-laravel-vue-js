<?php

namespace Modules\Meeting\Policies;

use App\Models\User;
use Modules\Auth\Enums\UserRole;
use Modules\Meeting\Models\Meeting;
use Modules\Meeting\Services\MeetingAccessGuard;
use Modules\Timetable\Models\TimetableEntry;

class MeetingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('meeting.view');
    }

    public function view(User $user, Meeting $meeting): bool
    {
        return $user->can('meeting.view')
            && app(MeetingAccessGuard::class)->allows($user, $meeting);
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

    /**
     * Ending a live room kicks every participant out, so it stays with the
     * meeting's own teacher and the admins — never a student or guardian,
     * whose role grants meeting.view only.
     */
    public function finish(User $user, Meeting $meeting): bool
    {
        return $user->can('meeting.update') && $this->ownsOrManages($user, $meeting);
    }

    public function cancel(User $user, Meeting $meeting): bool
    {
        return $user->can('meeting.update') && $this->ownsOrManages($user, $meeting);
    }

    /**
     * Who may actually enter the Jitsi room.
     *
     * A teacher who owns the meeting (and any admin/owner) can open the room
     * while it is still scheduled — somebody has to be first in. Everyone
     * else may only join once it is live, and never after it has finished or
     * been cancelled. Class membership is enforced separately by the
     * controller through PortalScopeService, because the policy has no view
     * of which classes a student belongs to.
     */
    public function join(User $user, Meeting $meeting): bool
    {
        if (! $user->can('meeting.view')) {
            return false;
        }

        if (in_array($meeting->status, ['finished', 'cancelled'], true)) {
            return false;
        }

        if ($this->ownsOrManages($user, $meeting)) {
            return true;
        }

        return $meeting->status === 'live';
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
