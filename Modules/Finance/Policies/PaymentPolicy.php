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

    /**
     * Turning a request into received money is the school's call, never the
     * payer's. A guardian holds only `payment.view`, so the update ability is
     * what keeps them out of their own cash confirmation.
     */
    public function confirm(User $user, Payment $payment): bool
    {
        return $user->can('payment.update');
    }

    public function cancel(User $user, Payment $payment): bool
    {
        return $user->can('payment.update');
    }

    /**
     * A parent may open a request against an invoice belonging to one of their
     * own children. Ownership is re-checked here even though the controller
     * scopes the query, so the ability cannot be reached with a foreign id.
     */
    public function requestPayment(User $user, Payment $payment): bool
    {
        return $this->ownsStudent($payment->student_id);
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
