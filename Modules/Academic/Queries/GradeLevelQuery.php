<?php

namespace Modules\Academic\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Academic\Models\GradeLevel;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Core\Support\Filters\BooleanFilter;
use Modules\Core\Support\Filters\Filter;
use Modules\Core\Support\Filters\NumericFilter;
use Modules\Core\Support\Filters\TranslatableFilter;

class GradeLevelQuery extends BaseQuery
{
    /**
     * `name` and `description` are JSON keyed by locale, so the term is matched
     * against each enabled language's value rather than the raw column. The
     * locale list comes from the Language settings, so adding a language makes
     * it searchable without touching this class.
     *
     * @var array<int, string>
     */
    protected array $translatableSearchFields = ['name', 'description'];

    /** @var array<int, string> */
    protected array $sortableFields = [
        'display_order',
        'is_active',
        'created_at',
    ];

    protected function baseQuery(): Builder
    {
        return GradeLevel::query();
    }

    /**
     * @return array<string, Filter>
     */
    protected function allowedFilters(): array
    {
        return [
            'is_active' => BooleanFilter::make()->label('gradeLevels.fields.isActive'),
            'name' => TranslatableFilter::make()->label('gradeLevels.fields.name'),
            'display_order' => NumericFilter::make()->label('gradeLevels.fields.displayOrder'),
        ];
    }

    /** Archived rows are listed alongside live ones so they can be restored. */
    public function withArchived(): self
    {
        $this->query->withTrashed();

        return $this;
    }
}
