<?php

namespace Modules\Finance\Policies;

use App\Models\User;
use Modules\Finance\Models\Refund;
use Modules\Portal\Traits\ChecksPortalOwnership;

/**
 * Recording a refund is a financial decision reserved for staff who hold
 * `refund.create`. A guardian is granted no refund ability at all, so they can
 * neither create one nor see another family's.
 */
class RefundPolicy
{
    use ChecksPortalOwnership;

    public function viewAny(User $user): bool
    {
        return $user->can('refund.view');
    }

    public function view(User $user, Refund $refund): bool
    {
        return $user->can('refund.view') && $this->ownsStudent($refund->student_id);
    }

    public function create(User $user): bool
    {
        return $user->can('refund.create');
    }

    public function delete(User $user, Refund $refund): bool
    {
        return $user->can('refund.delete');
    }
}
