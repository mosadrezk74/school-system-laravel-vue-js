<?php

namespace Modules\Finance\Actions;

use Modules\Academic\Services\AcademicYearService;
use Modules\Core\Abstracts\BaseAction;
use Modules\Finance\Queries\FinanceDashboardQuery;

class GetFinanceDashboardAction extends BaseAction
{
    public function __construct(
        protected FinanceDashboardQuery $query,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(mixed $input = null): array
    {
        $academicYearId = AcademicYearService::id();

        return [
            'total_outstanding' => $this->query->totalOutstanding($academicYearId),
            'total_paid' => $this->query->totalPaid($academicYearId),
            'todays_payments' => $this->query->todaysPayments($academicYearId),
            'monthly_revenue' => $this->query->monthlyRevenue($academicYearId),
            'pending_invoices' => $this->query->pendingInvoices($academicYearId),
            'overdue_invoices' => $this->query->overdueInvoices($academicYearId),
        ];
    }
}
