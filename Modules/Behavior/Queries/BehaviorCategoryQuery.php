<?php

namespace Modules\Behavior\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Behavior\Enums\BehaviorType;
use Modules\Behavior\Models\BehaviorCategory;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Core\Support\Filters\BooleanFilter;
use Modules\Core\Support\Filters\EnumFilter;
use Modules\Core\Support\Filters\Filter;
use Modules\Core\Support\Filters\NumericFilter;
use Modules\Core\Support\Filters\TranslatableFilter;

class BehaviorCategoryQuery extends BaseQuery
{
    /**
     * `name` is JSON keyed by locale, matched through each enabled language.
     *
     * @var array<int, string>
     */
    protected array $translatableSearchFields = ['name'];

    /** @var array<int, string> */
    protected array $sortableFields = ['type', 'points', 'is_active', 'created_at'];

    protected function baseQuery(): Builder
    {
        return BehaviorCategory::query();
    }

    /**
     * @return array<string, Filter>
     */
    protected function allowedFilters(): array
    {
        return [
            'type' => EnumFilter::make(BehaviorType::class)->label('behaviorCategories.type'),
            'is_active' => BooleanFilter::make()->label('behaviorCategories.active'),
            'name' => TranslatableFilter::make()->label('behaviorCategories.name'),
            'points' => NumericFilter::make()->label('behaviorCategories.points'),
        ];
    }
}
