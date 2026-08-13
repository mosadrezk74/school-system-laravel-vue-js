<?php

namespace Modules\Finance\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Core\Support\Filters\DateRangeFilter;
use Modules\Core\Support\Filters\EnumFilter;
use Modules\Core\Support\Filters\ExactFilter;
use Modules\Core\Support\Filters\Filter;
use Modules\Core\Support\Filters\NumericFilter;
use Modules\Finance\Enums\InvoiceStatus;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Support\Filters\OverdueInvoiceFilter;

class InvoiceQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $searchableFields = ['invoice_number'];

    /** @var array<string, array<int, string>> */
    protected array $searchableRelations = [
        'student.user' => ['name'],
    ];

    /** @var array<int, string> */
    protected array $sortableFields = [
        'invoice_number',
        'issue_date',
        'due_date',
        'total_amount',
        'balance_due',
        'status',
        'created_at',
    ];

    protected function baseQuery(): Builder
    {
        return Invoice::query()->with(['student.user']);
    }

    /**
     * `notes` and `currency` are left out: notes are free-text bookkeeping
     * rather than an axis anyone lists along, and the school bills in one
     * currency, so the column would filter nothing.
     *
     * @return array<string, Filter>
     */
    protected function allowedFilters(): array
    {
        return [
            'student_id' => ExactFilter::make()->label('finance.invoices.student'),
            'status' => EnumFilter::make(InvoiceStatus::class)->label('finance.invoices.status'),

            // `issued_*` / `due_*` rather than `issue_date_*`: these are the
            // parameter names the page has always sent.
            'issued' => DateRangeFilter::make()->column('issue_date')->label('finance.invoices.issueDate'),
            'due' => DateRangeFilter::make()->column('due_date')->label('finance.invoices.dueDate'),

            'total_amount' => NumericFilter::make()->label('finance.invoices.total'),
            'balance_due' => NumericFilter::make()->label('finance.invoices.balance'),
            'overdue_only' => OverdueInvoiceFilter::make()->label('finance.invoices.overdueOnly'),
        ];
    }

    /**
     * Invoices no longer store an academic year; they inherit it from the
     * student they bill, so the active year is applied through that relation.
     */
    public function academicYearId(?int $academicYearId): self
    {
        if ($academicYearId) {
            $this->query->whereHas('student', function (Builder $query) use ($academicYearId) {
                $query->where('academic_year_id', $academicYearId);
            });
        }

        return $this;
    }

    /**
     * Restricts to a fixed set of students. An empty array means "none",
     * never "unfiltered", so a guardian with no linked children sees nothing.
     *
     * Portal scoping, not a user-facing filter: it must not be reachable from
     * the query string, which is why it stays a method rather than joining
     * allowedFilters().
     *
     * @param  array<int, int>|null  $studentIds
     */
    public function studentIds(?array $studentIds): self
    {
        if ($studentIds === null) {
            return $this;
        }

        $studentIds === []
            ? $this->query->whereRaw('1 = 0')
            : $this->query->whereIn('student_id', $studentIds);

        return $this;
    }
}
