<?php

namespace Modules\Finance\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Core\Support\Filters\BooleanFilter;
use Modules\Core\Support\Filters\DateRangeFilter;
use Modules\Core\Support\Filters\EnumFilter;
use Modules\Core\Support\Filters\ExactFilter;
use Modules\Core\Support\Filters\Filter;
use Modules\Core\Support\Filters\NumericFilter;
use Modules\Finance\Enums\DiscountType;
use Modules\Finance\Models\FeeDiscount;

class FeeDiscountQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $searchableFields = ['name', 'code'];

    /** @var array<int, string> */
    protected array $sortableFields = ['name', 'discount_value', 'start_date', 'end_date', 'is_active', 'created_at'];

    protected function baseQuery(): Builder
    {
        return FeeDiscount::query()->with(['gradeLevel', 'classroom']);
    }

    /**
     * `used_count` is filterable but `usage_limit` is not: how many times a
     * discount has been taken up is something staff look for ("which ones are
     * unused?"), whereas the configured cap is a property of the record they
     * would open it to see.
     *
     * @return array<string, Filter>
     */
    protected function allowedFilters(): array
    {
        return [
            'discount_type' => EnumFilter::make(DiscountType::class)->label('finance.feeDiscounts.type'),
            'grade_level_id' => ExactFilter::make()->label('finance.feeDiscounts.gradeLevel')->nullable(),
            'classroom_id' => ExactFilter::make()->label('finance.feeDiscounts.classroom')->nullable(),
            'is_active' => BooleanFilter::make()->label('finance.feeDiscounts.active'),
            'discount_value' => NumericFilter::make()->label('finance.feeDiscounts.value'),
            'used_count' => NumericFilter::make()->label('finance.feeDiscounts.usedCount'),
            'start_date' => DateRangeFilter::make()->label('finance.feeDiscounts.startDate'),
            'end_date' => DateRangeFilter::make()->label('finance.feeDiscounts.endDate'),
        ];
    }
}
