<?php

namespace Modules\Finance\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Finance\Models\Payment;

class PaymentQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $searchableFields = ['reference_number'];

    /** @var array<int, string> */
    protected array $filterableFields = ['student_id', 'invoice_id', 'payment_method'];

    /** @var array<int, string> */
    protected array $sortableFields = ['amount', 'paid_at', 'payment_method', 'created_at'];

    protected function baseQuery(): Builder
    {
        return Payment::query()->with(['student.user', 'invoice']);
    }

    public function academicYearId(?int $academicYearId): self
    {
        if ($academicYearId) {
            $this->query->whereHas('student', function (Builder $query) use ($academicYearId) {
                $query->where('academic_year_id', $academicYearId);
            });
        }

        return $this;
    }

    public function searchReference(?string $term): self
    {
        if (! $term) {
            return $this;
        }

        $this->query->where(function (Builder $query) use ($term) {
            $query->where('reference_number', 'LIKE', "%{$term}%")
                ->orWhereHas('invoice', function (Builder $invoiceQuery) use ($term) {
                    $invoiceQuery->where('invoice_number', 'LIKE', "%{$term}%");
                })
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

    public function invoiceId(?int $invoiceId): self
    {
        if ($invoiceId) {
            $this->query->where('invoice_id', $invoiceId);
        }

        return $this;
    }

    public function paymentMethod(?string $paymentMethod): self
    {
        if ($paymentMethod) {
            $this->query->where('payment_method', $paymentMethod);
        }

        return $this;
    }

    public function paidBetween(?string $from, ?string $to): self
    {
        if ($from) {
            $this->query->whereDate('paid_at', '>=', $from);
        }

        if ($to) {
            $this->query->whereDate('paid_at', '<=', $to);
        }

        return $this;
    }
}
