<?php

namespace Modules\Setting\Policies;

use App\Models\User;

class SchoolSettingPolicy
{
    public function view(User $user): bool
    {
        return $user->can('setting.view');
    }

    public function update(User $user): bool
    {
        return $user->can('setting.update');
    }
}
