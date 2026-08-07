<?php

namespace Modules\Finance\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Finance\Actions\GetCategoryRevenueReportAction;
use Modules\Finance\Actions\GetDailyPaymentsReportAction;
use Modules\Finance\Actions\GetMonthlyRevenueReportAction;
use Modules\Finance\Actions\GetOutstandingFeesReportAction;
use Modules\Finance\Actions\GetStudentLedgerReportAction;
use Modules\Finance\DTOs\ReportFilterDTO;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Transformers\InvoiceResource;
use Modules\Finance\Transformers\PaymentResource;

class FinanceReportController extends BaseInertiaController
{
    public function outstandingFees(Request $request, GetOutstandingFeesReportAction $action): Response
    {
        $this->authorize('viewAny', Invoice::class);

        $rows = $action->execute(ReportFilterDTO::fromRequest($request));

        return $this->render('Finance::Reports/OutstandingFees', [
            'rows' => InvoiceResource::collection($rows),
            'filters' => $this->filterParams($request),
        ]);
    }

    public function studentLedger(Request $request, GetStudentLedgerReportAction $action): Response
    {
        $this->authorize('viewAny', Invoice::class);

        $rows = $action->execute(ReportFilterDTO::fromRequest($request));

        return $this->render('Finance::Reports/StudentLedger', [
            'rows' => InvoiceResource::collection($rows),
            'filters' => $this->filterParams($request),
        ]);
    }

    public function dailyPayments(Request $request, GetDailyPaymentsReportAction $action): Response
    {
        $this->authorize('viewAny', Invoice::class);

        $rows = $action->execute(ReportFilterDTO::fromRequest($request));

        return $this->render('Finance::Reports/DailyPayments', [
            'rows' => PaymentResource::collection($rows),
            'filters' => $this->filterParams($request),
        ]);
    }

    public function monthlyRevenue(Request $request, GetMonthlyRevenueReportAction $action): Response
    {
        $this->authorize('viewAny', Invoice::class);

        return $this->render('Finance::Reports/MonthlyRevenue', [
            'rows' => $action->execute(ReportFilterDTO::fromRequest($request)),
            'filters' => $this->filterParams($request),
        ]);
    }

    public function categoryRevenue(Request $request, GetCategoryRevenueReportAction $action): Response
    {
        $this->authorize('viewAny', Invoice::class);

        return $this->render('Finance::Reports/CategoryRevenue', [
            'rows' => $action->execute(ReportFilterDTO::fromRequest($request)),
            'filters' => $this->filterParams($request),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function filterParams(Request $request): array
    {
        return $request->only([
            'student_id',
            'grade_level_id',
            'classroom_id',
            'fee_category_id',
            'date_from',
            'date_to',
            'year',
        ]);
    }
}
