<?php

namespace Modules\Core\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Finance\Enums\InvoiceStatus;
use Modules\Finance\Enums\PaymentStatus;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Models\Payment;

/**
 * Finance figures for the admin dashboard.
 *
 * Money actually collected is always measured through Payment::settled(), the
 * finance module's own definition of "the money reached the school", and is
 * reported net of refunds. A parent's pending payment request therefore never
 * appears as revenue, and a refunded payment does not inflate the total.
 *
 * Invoices carry no academic year of their own, so year scoping goes through
 * the owning student — the same join the finance module uses.
 */
class DashboardFinanceQuery
{
    /**
     * Whether any invoice exists at all for the scope.
     *
     * Lets the page distinguish "nothing has been billed yet" from a genuine
     * zero balance, which are very different messages for an administrator.
     */
    public function hasInvoices(?int $academicYearId): bool
    {
        return $this->invoicesFor($academicYearId)->exists();
    }

    /**
     * Money still owed: the unpaid balance across every live invoice.
     *
     * Cancelled invoices are excluded because they are no longer collectable.
     *
     * @return array{amount: float, invoices: int}
     */
    public function outstanding(?int $academicYearId): array
    {
        $query = $this->collectableInvoices($academicYearId)->where('balance_due', '>', 0);

        return [
            'amount' => (float) (clone $query)->sum('balance_due'),
            'invoices' => (clone $query)->count(),
        ];
    }

    /**
     * Invoices past their due date with money still owed.
     *
     * Derived from the date and balance rather than the stored status, so an
     * invoice whose status has not yet been swept to "overdue" is still caught.
     */
    public function overdue(?int $academicYearId): array
    {
        $query = $this->collectableInvoices($academicYearId)
            ->where('balance_due', '>', 0)
            ->whereDate('due_date', '<', now()->toDateString());

        return [
            'amount' => (float) (clone $query)->sum('balance_due'),
            'invoices' => (clone $query)->count(),
        ];
    }

    /** Net settled revenue collected within the current calendar month. */
    public function collectedThisMonth(?int $academicYearId): float
    {
        return $this->netSettled(
            $this->settledPayments($academicYearId)
                ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])
        );
    }

    /** Net settled revenue collected today. */
    public function collectedToday(?int $academicYearId): float
    {
        return $this->netSettled(
            $this->settledPayments($academicYearId)->whereDate('paid_at', now()->toDateString())
        );
    }

    /**
     * Payment requests awaiting confirmation — money claimed but not banked,
     * and the finance team's actionable queue.
     *
     * @return array{count: int, amount: float}
     */
    public function awaitingConfirmation(?int $academicYearId): array
    {
        $query = $this->paymentsFor($academicYearId)
            ->where('status', PaymentStatus::Pending->value);

        return [
            'count' => (clone $query)->count(),
            'amount' => (float) (clone $query)->sum('amount'),
        ];
    }

    /**
     * Net revenue per month over the last $months months, oldest first.
     *
     * Grouped in SQL so the series costs one query. Months with no settled
     * payment are omitted rather than invented as zero.
     *
     * @return array<int, array{month: string, amount: float}>
     */
    public function monthlyCollection(?int $academicYearId, int $months = 6): array
    {
        return $this->settledPayments($academicYearId)
            ->where('paid_at', '>=', now()->subMonths($months - 1)->startOfMonth())
            ->selectRaw("DATE_FORMAT(paid_at, '%Y-%m') as month")
            ->selectRaw('COALESCE(SUM(amount - COALESCE(refunded_amount, 0)), 0) as amount')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn (object $row): array => [
                'month' => (string) $row->month,
                'amount' => round((float) $row->amount, 2),
            ])
            ->all();
    }

    /**
     * @param  Builder<Payment>  $query
     */
    private function netSettled(Builder $query): float
    {
        return round((float) $query->sum(
            $query->getQuery()->raw('amount - COALESCE(refunded_amount, 0)')
        ), 2);
    }

    /**
     * @return Builder<Payment>
     */
    private function settledPayments(?int $academicYearId): Builder
    {
        return $this->paymentsFor($academicYearId)->settled()->whereNotNull('paid_at');
    }

    /**
     * @return Builder<Payment>
     */
    private function paymentsFor(?int $academicYearId): Builder
    {
        return Payment::query()->when($academicYearId, fn (Builder $query) => $query->whereHas(
            'student',
            fn (Builder $student) => $student->where('academic_year_id', $academicYearId),
        ));
    }

    /**
     * Invoices that still represent collectable money.
     *
     * @return Builder<Invoice>
     */
    private function collectableInvoices(?int $academicYearId): Builder
    {
        return $this->invoicesFor($academicYearId)
            ->whereNotIn('status', [InvoiceStatus::Paid->value, InvoiceStatus::Cancelled->value]);
    }

    /**
     * @return Builder<Invoice>
     */
    private function invoicesFor(?int $academicYearId): Builder
    {
        return Invoice::query()->when($academicYearId, fn (Builder $query) => $query->whereHas(
            'student',
            fn (Builder $student) => $student->where('academic_year_id', $academicYearId),
        ));
    }
}
