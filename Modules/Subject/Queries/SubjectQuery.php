<?php

namespace Modules\Subject\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Core\Support\Filters\BooleanFilter;
use Modules\Core\Support\Filters\Filter;
use Modules\Core\Support\Filters\TranslatableFilter;
use Modules\Subject\Models\Subject;

class SubjectQuery extends BaseQuery
{
    /**
     * `name` and `description` are JSON keyed by locale, so they are matched
     * through each enabled language rather than as plain columns. The locale
     * list comes from the Language settings, so enabling a language makes it
     * searchable without touching this class.
     *
     * @var array<int, string>
     */
    protected array $translatableSearchFields = ['name', 'description'];

    /*
     * `name` is absent: it is a JSON column, so ordering by it would sort by
     * the raw document rather than by the visible text.
     */
    /** @var array<int, string> */
    protected array $sortableFields = ['is_active', 'created_at'];

    protected function baseQuery(): Builder
    {
        return Subject::query();
    }

    /**
     * @return array<string, Filter>
     */
    protected function allowedFilters(): array
    {
        return [
            'is_active' => BooleanFilter::make()->label('subject.fields.is_active'),
            'name' => TranslatableFilter::make()->label('subject.fields.name'),
        ];
    }
}
