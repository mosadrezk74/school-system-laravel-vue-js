<?php

namespace Modules\Finance\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Core\Support\Filters\DateRangeFilter;
use Modules\Core\Support\Filters\ExactFilter;
use Modules\Core\Support\Filters\Filter;
use Modules\Core\Support\Filters\NumericFilter;
use Modules\Core\Support\Filters\TextFilter;
use Modules\Finance\Models\Refund;

class RefundQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $searchableFields = ['reason'];

    /** @var array<string, array<int, string>> */
    protected array $searchableRelations = [
        'student.user' => ['name'],
        'payment' => ['reference_number'],
    ];

    /** @var array<int, string> */
    protected array $sortableFields = ['refund_date', 'amount', 'created_at'];

    protected string $defaultSortField = 'refund_date';

    protected function baseQuery(): Builder
    {
        return Refund::query()->with(['student.user', 'payment']);
    }

    /**
     * `notes` stays out: unlike `reason`, which is the refund's stated cause
     * and worth searching, notes are internal bookkeeping.
     *
     * @return array<string, Filter>
     */
    protected function allowedFilters(): array
    {
        return [
            'student_id' => ExactFilter::make()->label('finance.refunds.student'),
            'payment_id' => ExactFilter::make()->label('finance.refunds.payment'),
            'refund_method' => TextFilter::make()->label('finance.refunds.method'),
            'refund_date' => DateRangeFilter::make()->label('finance.refunds.date'),
            'amount' => NumericFilter::make()->label('finance.refunds.amount'),
        ];
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
