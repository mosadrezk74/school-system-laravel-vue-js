<?php

namespace Modules\Behavior\Policies;

use App\Models\User;
use Modules\Behavior\Models\BehaviorRecord;
use Modules\Portal\Traits\ChecksPortalOwnership;

class BehaviorRecordPolicy
{
    use ChecksPortalOwnership;

    public function viewAny(User $user): bool
    {
        return $user->can('behavior-record.view');
    }

    public function view(User $user, BehaviorRecord $record): bool
    {
        return $user->can('behavior-record.view') && $this->ownsStudent($record->student_id);
    }

    public function create(User $user): bool
    {
        return $user->can('behavior-record.create');
    }

    public function update(User $user, BehaviorRecord $record): bool
    {
        return $user->can('behavior-record.update');
    }

    public function delete(User $user, BehaviorRecord $record): bool
    {
        return $user->can('behavior-record.delete');
    }
}
