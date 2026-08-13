<?php

namespace Modules\Finance\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Core\Support\Filters\DateRangeFilter;
use Modules\Core\Support\Filters\EnumFilter;
use Modules\Core\Support\Filters\ExactFilter;
use Modules\Core\Support\Filters\Filter;
use Modules\Core\Support\Filters\NumericFilter;
use Modules\Core\Support\Filters\TextFilter;
use Modules\Finance\Enums\PaymentMethod;
use Modules\Finance\Enums\PaymentStatus;
use Modules\Finance\Models\Payment;

class PaymentQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $searchableFields = ['reference_number'];

    /** @var array<string, array<int, string>> */
    protected array $searchableRelations = [
        'invoice' => ['invoice_number'],
        'student.user' => ['name'],
    ];

    /** @var array<int, string> */
    protected array $sortableFields = ['amount', 'paid_at', 'payment_method', 'status', 'created_at'];

    protected string $defaultSortField = 'paid_at';

    protected function baseQuery(): Builder
    {
        return Payment::query()->with(['student.user', 'invoice']);
    }

    /**
     * `transaction_id` and `gateway_response` stay out: they are gateway
     * plumbing, and the raw response is an opaque JSON blob no user filters by.
     *
     * @return array<string, Filter>
     */
    protected function allowedFilters(): array
    {
        return [
            'student_id' => ExactFilter::make()->label('finance.payments.student'),
            'invoice_id' => ExactFilter::make()->label('finance.payments.invoice'),
            // Two columns describe how a payment was made: the legacy
            // `payment_method` string kept for existing rows, and the
            // `payment_method_id` FK the configurable methods introduced.
            // Both are offered rather than guessing which one a row uses.
            'payment_method' => EnumFilter::make(PaymentMethod::class)->label('finance.payments.method'),
            'payment_method_id' => ExactFilter::make()->label('finance.payments.methodRecord')->nullable(),
            'status' => EnumFilter::make(PaymentStatus::class)->label('finance.payments.status'),

            // `paid_from` / `paid_to` are the parameter names already in use.
            'paid' => DateRangeFilter::make()->column('paid_at')->label('finance.payments.paidAt'),

            'amount' => NumericFilter::make()->label('finance.payments.amount'),
            'reference_number' => TextFilter::make()->label('finance.payments.reference'),
            'refunded_amount' => NumericFilter::make()->label('finance.payments.refunded'),
            'confirmed' => DateRangeFilter::make()->column('confirmed_at')->label('finance.payments.confirmedAt'),
        ];
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

    /**
     * Portal scoping, not a user-facing filter — see InvoiceQuery::studentIds().
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
