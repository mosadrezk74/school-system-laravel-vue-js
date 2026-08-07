<?php

namespace Modules\Finance\Policies;

use App\Models\User;
use Modules\Finance\Models\FeeDiscount;

class FeeDiscountPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('fee.view');
    }

    public function view(User $user, FeeDiscount $feeDiscount): bool
    {
        return $user->can('fee.view');
    }

    public function create(User $user): bool
    {
        return $user->can('fee.create');
    }

    public function update(User $user, FeeDiscount $feeDiscount): bool
    {
        return $user->can('fee.update');
    }

    public function delete(User $user, FeeDiscount $feeDiscount): bool
    {
        return $user->can('fee.delete');
    }

    public function restore(User $user, FeeDiscount $feeDiscount): bool
    {
        return $user->can('fee.delete');
    }
}
