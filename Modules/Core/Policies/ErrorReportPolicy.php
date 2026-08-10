<?php

namespace Modules\Core\Policies;

use App\Models\User;
use Modules\Core\Models\ErrorReport;

/**
 * System diagnostics are staff-only. Portal accounts (students, guardians)
 * hold none of these permissions, so they are refused by the same check that
 * governs every other resource.
 */
class ErrorReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('error-report.view');
    }

    public function view(User $user, ErrorReport $errorReport): bool
    {
        return $user->can('error-report.view');
    }

    public function update(User $user, ErrorReport $errorReport): bool
    {
        return $user->can('error-report.update');
    }

    /** Stack traces expose internal paths, so they need their own grant. */
    public function viewTrace(User $user): bool
    {
        return $user->can('error-report.view-trace');
    }
}
