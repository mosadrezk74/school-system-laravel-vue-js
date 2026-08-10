<?php

namespace Modules\Finance\Policies;

use App\Models\User;
use Modules\Finance\Models\PaymentMethod;

/**
 * Managing payment methods is an admin concern. Parents read the active list
 * through the portal controller, which does not consult this policy — they
 * never hold `payment-method.*`, so every write here is closed to them.
 */
class PaymentMethodPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('payment-method.view');
    }

    public function view(User $user, PaymentMethod $paymentMethod): bool
    {
        return $user->can('payment-method.view');
    }

    public function create(User $user): bool
    {
        return $user->can('payment-method.create');
    }

    public function update(User $user, PaymentMethod $paymentMethod): bool
    {
        return $user->can('payment-method.update');
    }

    public function delete(User $user, PaymentMethod $paymentMethod): bool
    {
        return $user->can('payment-method.delete');
    }

    public function restore(User $user, PaymentMethod $paymentMethod): bool
    {
        return $user->can('payment-method.delete');
    }
}
