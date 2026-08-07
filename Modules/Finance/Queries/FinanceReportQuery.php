<?php

namespace Modules\Finance\Queries;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Finance\DTOs\ReportFilterDTO;
use Modules\Finance\Enums\InvoiceStatus;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Models\InvoiceItem;
use Modules\Finance\Models\Payment;

class FinanceReportQuery extends BaseQuery
{
    protected function baseQuery(): Builder
    {
        return Invoice::query();
    }

    /**
     * @return Collection<int, Invoice>
     */
    public function outstandingFees(ReportFilterDTO $filters, ?int $academicYearId): Collection
    {
        return $this->scopedInvoices($filters, $academicYearId)
            ->where('balance_due', '>', 0)
            ->whereNotIn('status', [InvoiceStatus::Paid->value, InvoiceStatus::Cancelled->value])
            ->with(['student.user', 'student.currentClass'])
            ->orderBy('due_date')
            ->get();
    }

    /**
     * @return Collection<int, Invoice>
     */
    public function studentLedger(ReportFilterDTO $filters, ?int $academicYearId): Collection
    {
        return $this->scopedInvoices($filters, $academicYearId)
            ->with(['items.feeStructure', 'payments'])
            ->orderBy('issue_date')
            ->get();
    }

    /**
     * @return Collection<int, Payment>
     */
    public function dailyPayments(ReportFilterDTO $filters, ?int $academicYearId): Collection
    {
        return $this->scopedPayments($filters, $academicYearId)
            ->with(['student.user', 'invoice'])
            ->orderByDesc('paid_at')
            ->get();
    }

    /**
     * @return Collection<int, object>
     */
    public function monthlyRevenue(ReportFilterDTO $filters, ?int $academicYearId): Collection
    {
        $year = $filters->year ?? now()->year;

        return $this->scopedPayments($filters, $academicYearId)
            ->whereYear('paid_at', $year)
            ->selectRaw('MONTH(paid_at) as month, SUM(amount) as total, COUNT(*) as payments_count')
            ->groupByRaw('MONTH(paid_at)')
            ->orderByRaw('MONTH(paid_at)')
            ->get();
    }

    /**
     * @return Collection<int, object>
     */
    public function categoryRevenue(ReportFilterDTO $filters, ?int $academicYearId): Collection
    {
        return InvoiceItem::query()
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->join('fee_structures', 'fee_structures.id', '=', 'invoice_items.fee_structure_id')
            ->join('fee_categories', 'fee_categories.id', '=', 'fee_structures.fee_category_id')
            ->join('student_profiles', 'student_profiles.id', '=', 'invoices.student_id')
            ->whereNull('invoices.deleted_at')
            ->where('invoices.status', '!=', InvoiceStatus::Cancelled->value)
            ->when($academicYearId, fn ($query) => $query->where('student_profiles.academic_year_id', $academicYearId))
            ->when($filters->feeCategoryId, fn ($query) => $query->where('fee_categories.id', $filters->feeCategoryId))
            ->when($filters->dateFrom, fn ($query) => $query->whereDate('invoices.issue_date', '>=', $filters->dateFrom))
            ->when($filters->dateTo, fn ($query) => $query->whereDate('invoices.issue_date', '<=', $filters->dateTo))
            ->selectRaw('fee_categories.id as fee_category_id, fee_categories.name as fee_category_name, SUM(invoice_items.total) as total, COUNT(*) as items_count')
            ->groupBy('fee_categories.id', 'fee_categories.name')
            ->orderByDesc('total')
            ->get();
    }

    private function scopedInvoices(ReportFilterDTO $filters, ?int $academicYearId): Builder
    {
        return Invoice::query()
            ->when($filters->studentId, fn (Builder $query) => $query->where('student_id', $filters->studentId))
            ->when($filters->dateFrom, fn (Builder $query) => $query->whereDate('issue_date', '>=', $filters->dateFrom))
            ->when($filters->dateTo, fn (Builder $query) => $query->whereDate('issue_date', '<=', $filters->dateTo))
            ->whereHas('student', function (Builder $student) use ($filters, $academicYearId) {
                $student
                    ->when($academicYearId, fn (Builder $query) => $query->where('academic_year_id', $academicYearId))
                    ->when($filters->classroomId, fn (Builder $query) => $query->where('current_class_id', $filters->classroomId))
                    ->when($filters->gradeLevelId, fn (Builder $query) => $query->whereHas(
                        'currentClass',
                        fn (Builder $class) => $class->where('grade_level_id', $filters->gradeLevelId),
                    ));
            });
    }

    private function scopedPayments(ReportFilterDTO $filters, ?int $academicYearId): Builder
    {
        return Payment::query()
            ->when($filters->studentId, fn (Builder $query) => $query->where('student_id', $filters->studentId))
            ->when($filters->dateFrom, fn (Builder $query) => $query->whereDate('paid_at', '>=', $filters->dateFrom))
            ->when($filters->dateTo, fn (Builder $query) => $query->whereDate('paid_at', '<=', $filters->dateTo))
            ->whereHas('student', function (Builder $student) use ($filters, $academicYearId) {
                $student
                    ->when($academicYearId, fn (Builder $query) => $query->where('academic_year_id', $academicYearId))
                    ->when($filters->classroomId, fn (Builder $query) => $query->where('current_class_id', $filters->classroomId))
                    ->when($filters->gradeLevelId, fn (Builder $query) => $query->whereHas(
                        'currentClass',
                        fn (Builder $class) => $class->where('grade_level_id', $filters->gradeLevelId),
                    ));
            });
    }
}
