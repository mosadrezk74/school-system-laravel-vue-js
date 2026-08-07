<?php

namespace Modules\Finance\Policies;

use App\Models\User;
use Modules\Finance\Models\FeeStructure;

class FeeStructurePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('fee.view');
    }

    public function view(User $user, FeeStructure $feeStructure): bool
    {
        return $user->can('fee.view');
    }

    public function create(User $user): bool
    {
        return $user->can('fee.create');
    }

    public function update(User $user, FeeStructure $feeStructure): bool
    {
        return $user->can('fee.update');
    }

    public function delete(User $user, FeeStructure $feeStructure): bool
    {
        return $user->can('fee.delete');
    }

    public function restore(User $user, FeeStructure $feeStructure): bool
    {
        return $user->can('fee.delete');
    }
}
