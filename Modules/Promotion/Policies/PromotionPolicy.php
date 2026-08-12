<?php

namespace Modules\Promotion\Policies;

use App\Models\User;
use Modules\Auth\Enums\UserRole;
use Modules\Promotion\Models\StudentPromotion;

/**
 * Promotions are an administrative act.
 *
 * Teachers, students and guardians have no access at all — not a narrowed
 * view, none. Moving a year group between academic years is a school-office
 * decision, so the account type is checked alongside the permission rather
 * than relying on permissions that other roles also hold.
 */
class PromotionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('student-promotion.view') && $this->isAdministrative($user);
    }

    public function view(User $user, StudentPromotion $promotion): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->can('student-promotion.create') && $this->isAdministrative($user);
    }

    /** Reverting undoes a completed promotion, so it is the stronger grant. */
    public function revert(User $user, StudentPromotion $promotion): bool
    {
        return $user->can('student-promotion.delete') && $this->isAdministrative($user);
    }

    private function isAdministrative(User $user): bool
    {
        return in_array($user->role, [UserRole::Owner, UserRole::Admin], true);
    }
}
