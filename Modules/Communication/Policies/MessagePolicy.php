<?php

namespace Modules\Communication\Policies;

use App\Models\User;
use Modules\Communication\Models\BroadcastMessage;

class MessagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('message.view');
    }

    public function view(User $user, BroadcastMessage $message): bool
    {
        return $user->can('message.view');
    }

    public function create(User $user): bool
    {
        return $user->can('message.create');
    }
}
