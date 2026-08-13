<?php

namespace Modules\Finance\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Core\Support\Filters\BooleanFilter;
use Modules\Core\Support\Filters\EnumFilter;
use Modules\Core\Support\Filters\ExactFilter;
use Modules\Core\Support\Filters\Filter;
use Modules\Core\Support\Filters\NumericFilter;
use Modules\Core\Support\Filters\TextFilter;
use Modules\Finance\Enums\FeeFrequency;
use Modules\Finance\Models\FeeStructure;

class FeeStructureQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $searchableFields = ['name'];

    /** @var array<int, string> */
    protected array $sortableFields = ['name', 'amount', 'frequency', 'due_day', 'created_at'];

    protected function baseQuery(): Builder
    {
        return FeeStructure::query()->with(['feeCategory', 'gradeLevel', 'classroom']);
    }

    /**
     * Unlike a class, a fee structure carries its own `academic_year_id` and
     * `semester_id` — they were re-added, nullable, to express a subscription
     * period. So the year is an explicit historical filter here rather than
     * something inherited through a relation, and "no period" is a real answer,
     * which is why both are `nullable()`.
     *
     * @return array<string, Filter>
     */
    protected function allowedFilters(): array
    {
        return [
            'academic_year_id' => ExactFilter::make()->label('finance.feeStructures.academicYear')->nullable(),
            'semester_id' => ExactFilter::make()->label('finance.feeStructures.semester')->nullable(),
            'fee_category_id' => ExactFilter::make()->label('finance.feeStructures.category'),
            'grade_level_id' => ExactFilter::make()->label('finance.feeStructures.gradeLevel')->nullable(),
            'classroom_id' => ExactFilter::make()->label('finance.feeStructures.classroom')->nullable(),
            'frequency' => EnumFilter::make(FeeFrequency::class)->label('finance.feeStructures.frequency'),
            'is_optional' => BooleanFilter::make()->label('finance.feeStructures.optional'),
            'is_active' => BooleanFilter::make()->label('finance.feeStructures.active'),
            'amount' => NumericFilter::make()->label('finance.feeStructures.amount'),
            'name' => TextFilter::make()->label('finance.feeStructures.name'),
        ];
    }
}
