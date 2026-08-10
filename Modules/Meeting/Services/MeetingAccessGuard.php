<?php

namespace Modules\Meeting\Services;

use App\Models\User;
use Modules\Auth\Enums\UserRole;
use Modules\Meeting\Models\Meeting;
use Modules\Portal\Services\PortalScopeService;

/**
 * Decides whether a signed-in user belongs in a given meeting's room.
 *
 * The policy answers "may this role join a meeting in this state"; this
 * answers "is this particular meeting one of *theirs*". Both must pass, so a
 * student holding meeting.view cannot enter another class's room by guessing
 * an id.
 */
final readonly class MeetingAccessGuard
{
    public function __construct(
        private PortalScopeService $scope,
    ) {}

    public function allows(User $user, Meeting $meeting): bool
    {
        // Staff (admin/owner) are unscoped, and a teacher always reaches the
        // meetings they run.
        if ($user->role === UserRole::Owner || $user->role === UserRole::Admin) {
            return true;
        }

        if ($meeting->teacher_id === $user->id) {
            return true;
        }

        $classIds = $this->scope->accessibleClassIds();

        // null means "no class restriction applies to this account".
        if ($classIds === null) {
            return true;
        }

        /*
         * A meeting with no class cannot be matched against a student's
         * enrolment, so it is treated as staff-only rather than public.
         */
        if ($meeting->school_class_id === null) {
            return false;
        }

        return in_array($meeting->school_class_id, $classIds, true);
    }
}
