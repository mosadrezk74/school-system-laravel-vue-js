<?php

namespace Modules\Behavior\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Behavior\Models\BehaviorCategory;
use Modules\Core\Abstracts\BaseQuery;

class BehaviorCategoryQuery extends BaseQuery
{
    /**
     * `name` is a Spatie-translatable JSON column. A LIKE against it matches
     * the stored JSON, which still finds the typed text for any locale — it
     * is the only searchable column left now that `code` is gone.
     */
    protected array $searchableFields = ['name'];

    /** @var array<int, string> */
    protected array $filterableFields = ['type', 'is_active'];

    /** @var array<int, string> */
    protected array $sortableFields = ['type', 'points', 'is_active', 'created_at'];

    protected function baseQuery(): Builder
    {
        return BehaviorCategory::query();
    }
}
