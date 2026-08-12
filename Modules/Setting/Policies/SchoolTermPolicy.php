<?php

namespace Modules\Setting\Policies;

use App\Models\User;
use Modules\Setting\Models\SchoolTerm;

/**
 * Managing the school's terms is an administrative act.
 *
 * Reading the published version is separate and deliberately open to every
 * signed-in user — families must be able to read what they are agreeing to —
 * so it is expressed as viewPublished() rather than the permissioned view().
 */
class SchoolTermPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('school-term.view');
    }

    public function view(User $user, SchoolTerm $term): bool
    {
        return $user->can('school-term.view');
    }

    public function create(User $user): bool
    {
        return $user->can('school-term.create');
    }

    /**
     * A published or archived version is frozen: its wording is the record of
     * what families agreed to. Editing means drafting the next version.
     */
    public function update(User $user, SchoolTerm $term): bool
    {
        return $user->can('school-term.update') && $term->status->isEditable();
    }

    public function delete(User $user, SchoolTerm $term): bool
    {
        return $user->can('school-term.delete') && $term->status->isEditable();
    }

    public function publish(User $user, SchoolTerm $term): bool
    {
        return $user->can('school-term.publish') && $term->isDraft();
    }

    /**
     * Any signed-in user may read the terms in force. Students and guardians
     * hold no school-term.* permission and must still see them.
     */
    public function viewPublished(User $user): bool
    {
        return true;
    }

    /**
     * Accepting is an act of the signed-in person on their own behalf; there
     * is no path to accepting for someone else, because the Action always
     * uses auth()->user().
     */
    public function accept(User $user): bool
    {
        return $user->isActive();
    }
}
