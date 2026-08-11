<?php

namespace Modules\Attendance\Policies;

use App\Models\User;
use Modules\Attendance\Models\AttendanceRecord;
use Modules\Portal\Traits\ChecksPortalOwnership;

class AttendancePolicy
{
    use ChecksPortalOwnership;

    public function viewAny(User $user): bool
    {
        return $user->can('attendance.view');
    }

    public function view(User $user, AttendanceRecord $record): bool
    {
        return $user->can('attendance.view') && $this->ownsClass($record->class_id);
    }

    public function create(User $user): bool
    {
        return $user->can('attendance.create');
    }

    public function delete(User $user, AttendanceRecord $record): bool
    {
        return $user->can('attendance.delete');
    }
}
