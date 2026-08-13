<?php

namespace Modules\Finance\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Core\Support\Filters\EnumFilter;
use Modules\Core\Support\Filters\ExactFilter;
use Modules\Core\Support\Filters\Filter;
use Modules\Core\Support\Filters\NumericFilter;
use Modules\Core\Support\Filters\RelationFilter;
use Modules\Finance\Enums\StudentFeeStatus;
use Modules\Finance\Models\StudentFee;

class StudentFeeQuery extends BaseQuery
{
    /** @var array<string, array<int, string>> */
    protected array $searchableRelations = [
        'student.user' => ['name'],
        'feeStructure' => ['name'],
    ];

    /** @var array<int, string> */
    protected array $sortableFields = ['amount', 'final_amount', 'discount_amount', 'status', 'created_at'];

    protected function baseQuery(): Builder
    {
        return StudentFee::query()->with(['student.user', 'feeStructure']);
    }

    /**
     * @return array<string, Filter>
     */
    protected function allowedFilters(): array
    {
        return [
            'student_id' => ExactFilter::make()->label('finance.studentFees.student'),
            'fee_structure_id' => ExactFilter::make()->label('finance.studentFees.feeStructure'),
            'status' => EnumFilter::make(StudentFeeStatus::class)->label('finance.studentFees.status'),

            // Through the structure, so "all transport fees" is one choice
            // rather than picking every structure that bills for transport.
            'fee_category_id' => RelationFilter::make('feeStructure', 'fee_category_id')
                ->label('finance.studentFees.feeCategory'),

            'final_amount' => NumericFilter::make()->label('finance.studentFees.finalAmount'),
            'discount_amount' => NumericFilter::make()->label('finance.studentFees.discount'),
        ];
    }
}
