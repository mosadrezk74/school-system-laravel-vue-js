<?php

namespace Modules\Finance\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Finance\Models\StudentFee;

class StudentFeeQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $filterableFields = ['student_id', 'fee_structure_id', 'status'];

    /** @var array<int, string> */
    protected array $sortableFields = ['amount', 'final_amount', 'status', 'created_at'];

    protected function baseQuery(): Builder
    {
        return StudentFee::query()->with(['student.user', 'feeStructure.feeCategory']);
    }

    public function studentId(?int $studentId): self
    {
        if ($studentId) {
            $this->query->where('student_id', $studentId);
        }

        return $this;
    }

    public function feeStructureId(?int $feeStructureId): self
    {
        if ($feeStructureId) {
            $this->query->where('fee_structure_id', $feeStructureId);
        }

        return $this;
    }

    public function status(?string $status): self
    {
        if ($status) {
            $this->query->where('status', $status);
        }

        return $this;
    }
}
