<?php

namespace Modules\Core\Policies;

use App\Models\User;
use Modules\Core\Models\Activity;

/**
 * The audit log is staff-only and read-only.
 *
 * Entries record who changed what across every module, so reading them is a
 * dedicated grant rather than something any signed-in user inherits. There is
 * deliberately no create/update/delete ability: the log is written by the
 * application and pruned by schedule, never edited through the UI.
 */
class ActivityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('activity.view');
    }

    public function view(User $user, Activity $activity): bool
    {
        return $user->can('activity.view');
    }
}
