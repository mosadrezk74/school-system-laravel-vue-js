<?php

namespace Modules\Report\Policies;

use App\Models\User;
use Modules\Report\Models\ReportTemplate;

class ReportTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('report.view');
    }

    public function view(User $user, ReportTemplate $reportTemplate): bool
    {
        return $user->can('report.view');
    }

    public function generate(User $user): bool
    {
        return $user->can('report.generate');
    }

    public function create(User $user): bool
    {
        return $user->can('report.manage');
    }

    public function update(User $user, ReportTemplate $reportTemplate): bool
    {
        return $user->can('report.manage');
    }

    public function delete(User $user, ReportTemplate $reportTemplate): bool
    {
        return $user->can('report.manage');
    }

    public function restore(User $user, ReportTemplate $reportTemplate): bool
    {
        return $user->can('report.manage');
    }
}
