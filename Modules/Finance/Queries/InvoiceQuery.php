<?php

namespace Modules\Finance\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Finance\Enums\InvoiceStatus;
use Modules\Finance\Models\Invoice;

class InvoiceQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $searchableFields = ['invoice_number'];

    /** @var array<int, string> */
    protected array $filterableFields = ['student_id', 'status'];

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

    public function searchNumber(?string $term): self
    {
        if (! $term) {
            return $this;
        }

        $this->query->where(function (Builder $query) use ($term) {
            $query->where('invoice_number', 'LIKE', "%{$term}%")
                ->orWhereHas('student.user', function (Builder $userQuery) use ($term) {
                    $userQuery->where('name', 'LIKE', "%{$term}%");
                });
        });

        return $this;
    }

    public function studentId(?int $studentId): self
    {
        if ($studentId) {
            $this->query->where('student_id', $studentId);
        }

        return $this;
    }

    /**
     * Restricts to a fixed set of students. An empty array means "none",
     * never "unfiltered", so a guardian with no linked children sees nothing.
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

    public function status(?string $status): self
    {
        if ($status) {
            $this->query->where('status', $status);
        }

        return $this;
    }

    public function issuedBetween(?string $from, ?string $to): self
    {
        if ($from) {
            $this->query->whereDate('issue_date', '>=', $from);
        }

        if ($to) {
            $this->query->whereDate('issue_date', '<=', $to);
        }

        return $this;
    }

    public function overdueOnly(?bool $overdueOnly): self
    {
        if ($overdueOnly) {
            $this->query
                ->whereDate('due_date', '<', now()->toDateString())
                ->where('balance_due', '>', 0)
                ->whereNotIn('status', [InvoiceStatus::Paid->value, InvoiceStatus::Cancelled->value]);
        }

        return $this;
    }
}
