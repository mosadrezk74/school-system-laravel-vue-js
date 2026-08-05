<?php

namespace Modules\Behavior\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Behavior\Models\BehaviorCategory;
use Modules\Core\Abstracts\BaseQuery;

class BehaviorCategoryQuery extends BaseQuery
{
    /**
     * `name` is a Spatie-translatable JSON column, so a plain LIKE against it
     * would match raw JSON rather than the human-readable text. There's no
     * existing translatable-column search precedent in this codebase
     * (AcademicYear's `name` is a plain string), so this searches `code`
     * only and skips name search rather than over-engineering a JSON search.
     */
    protected array $searchableFields = ['code'];

    /** @var array<int, string> */
    protected array $filterableFields = ['type', 'is_active'];

    /** @var array<int, string> */
    protected array $sortableFields = ['code', 'type', 'points', 'is_active', 'created_at'];

    protected function baseQuery(): Builder
    {
        return BehaviorCategory::query();
    }
}
