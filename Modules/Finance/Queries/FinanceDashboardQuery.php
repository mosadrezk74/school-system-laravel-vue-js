<?php

namespace Modules\Finance\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Finance\Enums\InvoiceStatus;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Models\Payment;

class FinanceDashboardQuery extends BaseQuery
{
    protected function baseQuery(): Builder
    {
        return Invoice::query();
    }

    public function totalOutstanding(?int $academicYearId): float
    {
        return (float) $this->invoicesForYear($academicYearId)
            ->whereNotIn('status', [InvoiceStatus::Paid->value, InvoiceStatus::Cancelled->value])
            ->sum('balance_due');
    }

    public function totalPaid(?int $academicYearId): float
    {
        return (float) $this->paymentsForYear($academicYearId)->sum('amount');
    }

    public function todaysPayments(?int $academicYearId): float
    {
        return (float) $this->paymentsForYear($academicYearId)
            ->whereDate('paid_at', now()->toDateString())
            ->sum('amount');
    }

    public function monthlyRevenue(?int $academicYearId): float
    {
        return (float) $this->paymentsForYear($academicYearId)
            ->whereYear('paid_at', now()->year)
            ->whereMonth('paid_at', now()->month)
            ->sum('amount');
    }

    public function pendingInvoices(?int $academicYearId): int
    {
        return $this->invoicesForYear($academicYearId)
            ->whereIn('status', [InvoiceStatus::Pending->value, InvoiceStatus::Partial->value])
            ->count();
    }

    public function overdueInvoices(?int $academicYearId): int
    {
        return $this->invoicesForYear($academicYearId)
            ->whereDate('due_date', '<', now()->toDateString())
            ->where('balance_due', '>', 0)
            ->whereNotIn('status', [InvoiceStatus::Paid->value, InvoiceStatus::Cancelled->value])
            ->count();
    }

    private function invoicesForYear(?int $academicYearId): Builder
    {
        return Invoice::query()->when($academicYearId, fn (Builder $query) => $query->whereHas(
            'student',
            fn (Builder $student) => $student->where('academic_year_id', $academicYearId),
        ));
    }

    private function paymentsForYear(?int $academicYearId): Builder
    {
        return Payment::query()->when($academicYearId, fn (Builder $query) => $query->whereHas(
            'student',
            fn (Builder $student) => $student->where('academic_year_id', $academicYearId),
        ));
    }
}
