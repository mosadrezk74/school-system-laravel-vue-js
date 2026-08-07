<?php

namespace Modules\Finance\Policies;

use App\Models\User;
use Modules\Finance\Models\Invoice;
use Modules\Portal\Traits\ChecksPortalOwnership;

class InvoicePolicy
{
    use ChecksPortalOwnership;

    public function viewAny(User $user): bool
    {
        return $user->can('invoice.view');
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $user->can('invoice.view') && $this->ownsStudent($invoice->student_id);
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
