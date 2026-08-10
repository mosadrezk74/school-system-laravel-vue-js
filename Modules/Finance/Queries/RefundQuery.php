<?php

namespace Modules\Finance\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Finance\Models\Refund;

class RefundQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $filterableFields = ['payment_id', 'student_id'];

    /** @var array<int, string> */
    protected array $sortableFields = ['refund_date', 'amount', 'created_at'];

    protected function baseQuery(): Builder
    {
        return Refund::query()->with(['payment', 'student.user']);
    }

    /**
     * Restricts the result to a fixed set of students. Portal callers pass the
     * ids resolved by PortalScopeService, so ownership is applied in the query
     * layer rather than trusted from the request.
     *
     * @param  array<int, int>|null  $studentIds
     */
    public function studentIds(?array $studentIds): self
    {
        if ($studentIds !== null) {
            $this->query->whereIn('student_id', $studentIds);
        }

        return $this;
    }

    public function paymentId(?int $paymentId): self
    {
        if ($paymentId) {
            $this->query->where('payment_id', $paymentId);
        }

        return $this;
    }
}
