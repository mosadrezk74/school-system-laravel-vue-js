<?php

namespace Modules\Setting\Policies;

use App\Models\User;
use Modules\Setting\Models\Language;

class LanguagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('language.view');
    }

    public function update(User $user, Language $language): bool
    {
        return $user->can('language.update');
    }
}
