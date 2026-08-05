<?php

namespace Modules\Behavior\Policies;

use App\Models\User;
use Modules\Behavior\Models\BehaviorRecord;

class BehaviorRecordPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('behavior-record.view');
    }

    public function view(User $user, BehaviorRecord $record): bool
    {
        return $user->can('behavior-record.view');
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
