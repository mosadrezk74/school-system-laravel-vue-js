<?php

namespace Modules\Report\Reports;

use Illuminate\Database\Eloquent\Builder;
use Modules\Academic\Services\AcademicYearService;
use Modules\Finance\Enums\InvoiceStatus;
use Modules\Finance\Models\Invoice;
use Modules\Report\DTOs\ReportFilterDTO;
use Modules\Report\DTOs\ReportResultDTO;
use Modules\Report\Enums\ReportCategory;
use Modules\Report\Enums\ReportColumnType;
use Modules\Report\Enums\ReportFilterType;

class InvoiceReportProvider extends AbstractReportProvider
{
    public function code(): string
    {
        return 'finance.invoices';
    }

    public function category(): string
    {
        return ReportCategory::Finance->value;
    }

    public function filters(): array
    {
        return [
            $this->filter('status', ReportFilterType::Select, options: $this->statusOptions()),
            $this->filter('class_id', ReportFilterType::Select, options: $this->classOptions()),
            $this->filter('date_from', ReportFilterType::Date),
            $this->filter('date_to', ReportFilterType::Date),
        ];
    }

    public function columns(): array
    {
        return [
            $this->column('invoice_number'),
            $this->column('student_name'),
            $this->column('issue_date', ReportColumnType::Date),
            $this->column('due_date', ReportColumnType::Date),
            $this->column('total_amount', ReportColumnType::Money),
            $this->column('paid_amount', ReportColumnType::Money),
            $this->column('balance_due', ReportColumnType::Money),
            $this->column('status', ReportColumnType::Badge),
        ];
    }

    public function generate(ReportFilterDTO $filters): ReportResultDTO
    {
        $academicYearId = AcademicYearService::id();

        $invoices = Invoice::query()
            ->with(['student.user'])
            ->when($filters->string('status'), fn (Builder $q, $status) => $q->where('status', $status))
            ->when($filters->string('date_from'), fn (Builder $q, $date) => $q->whereDate('issue_date', '>=', $date))
            ->when($filters->string('date_to'), fn (Builder $q, $date) => $q->whereDate('issue_date', '<=', $date))
            ->whereHas('student', function (Builder $student) use ($filters, $academicYearId) {
                $student
                    ->when($academicYearId, fn (Builder $q) => $q->where('academic_year_id', $academicYearId))
                    ->when($filters->int('class_id'), fn (Builder $q, $id) => $q->where('current_class_id', $id));
            })
            ->orderByDesc('issue_date')
            ->get();

        $rows = $invoices
            ->map(fn (Invoice $invoice): array => [
                'invoice_number' => $invoice->invoice_number,
                'student_name' => $invoice->student?->user?->name,
                'issue_date' => $invoice->issue_date?->toDateString(),
                'due_date' => $invoice->due_date?->toDateString(),
                'total_amount' => (float) $invoice->total_amount,
                'paid_amount' => (float) $invoice->paid_amount,
                'balance_due' => (float) $invoice->balance_due,
                'status' => $invoice->status?->label(),
            ])
            ->all();

        return new ReportResultDTO(
            rows: $rows,
            columns: $this->columns(),
            summary: [
                'invoices' => $invoices->count(),
                'total_billed' => round((float) $invoices->sum('total_amount'), 2),
                'total_paid' => round((float) $invoices->sum('paid_amount'), 2),
                'total_outstanding' => round((float) $invoices->sum('balance_due'), 2),
            ],
            metadata: [
                'code' => $this->code(),
                'academic_year_id' => $academicYearId,
                'filters' => $filters->toArray(),
            ],
        );
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function statusOptions(): array
    {
        return InvoiceStatus::options();
    }
}
