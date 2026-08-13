<?php

namespace Modules\Finance\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Core\Support\Filters\BooleanFilter;
use Modules\Core\Support\Filters\Filter;
use Modules\Core\Support\Filters\TextFilter;
use Modules\Core\Support\Filters\TranslatableFilter;
use Modules\Finance\Models\FeeCategory;

class FeeCategoryQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $searchableFields = ['code'];

    /**
     * `name` and `description` are JSON keyed by locale.
     *
     * @var array<int, string>
     */
    protected array $translatableSearchFields = ['name', 'description'];

    /** @var array<int, string> */
    protected array $sortableFields = ['code', 'is_active', 'created_at'];

    protected function baseQuery(): Builder
    {
        return FeeCategory::query();
    }

    /**
     * @return array<string, Filter>
     */
    protected function allowedFilters(): array
    {
        return [
            'is_active' => BooleanFilter::make()->label('finance.feeCategories.active'),
            'name' => TranslatableFilter::make()->label('finance.feeCategories.name'),
            'code' => TextFilter::make()->label('finance.feeCategories.code'),
        ];
    }
}
