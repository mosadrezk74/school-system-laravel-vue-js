<?php

namespace Modules\Finance\Policies;

use App\Models\User;
use Modules\Finance\Models\Payment;

class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('payment.view');
    }

    public function view(User $user, Payment $payment): bool
    {
        return $user->can('payment.view');
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
