<?php

namespace Modules\Finance\Policies;

use App\Models\User;
use Modules\Finance\Models\Invoice;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('invoice.view');
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $user->can('invoice.view');
    }

    public function create(User $user): bool
    {
        return $user->can('invoice.create');
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $user->can('invoice.update');
    }

    public function cancel(User $user, Invoice $invoice): bool
    {
        return $user->can('invoice.update');
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $user->can('invoice.delete');
    }

    public function restore(User $user, Invoice $invoice): bool
    {
        return $user->can('invoice.delete');
    }
}
