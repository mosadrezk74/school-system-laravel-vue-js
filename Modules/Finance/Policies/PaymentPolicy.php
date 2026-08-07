<?php

namespace Modules\Finance\Policies;

use App\Models\User;
use Modules\Finance\Models\Payment;
use Modules\Portal\Traits\ChecksPortalOwnership;

class PaymentPolicy
{
    use ChecksPortalOwnership;

    public function viewAny(User $user): bool
    {
        return $user->can('payment.view');
    }

    public function view(User $user, Payment $payment): bool
    {
        return $user->can('payment.view') && $this->ownsStudent($payment->student_id);
    }

    public function create(User $user): bool
    {
        return $user->can('payment.create');
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $user->can('payment.delete');
    }

    public function restore(User $user, Payment $payment): bool
    {
        return $user->can('payment.delete');
    }
}
