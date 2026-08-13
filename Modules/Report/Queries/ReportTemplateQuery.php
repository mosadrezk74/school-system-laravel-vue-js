<?php

namespace Modules\Report\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Core\Support\Filters\BooleanFilter;
use Modules\Core\Support\Filters\EnumFilter;
use Modules\Core\Support\Filters\ExactFilter;
use Modules\Core\Support\Filters\Filter;
use Modules\Core\Support\Filters\TextFilter;
use Modules\Core\Support\Filters\TranslatableFilter;
use Modules\Report\Enums\ReportCategory;
use Modules\Report\Models\ReportTemplate;

class ReportTemplateQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $searchableFields = ['code'];

    /**
     * `name` became a translatable JSON column.
     *
     * @var array<int, string>
     */
    protected array $translatableSearchFields = ['name'];

    /** @var array<int, string> */
    protected array $sortableFields = ['code', 'category', 'is_active', 'created_at'];

    protected function baseQuery(): Builder
    {
        return ReportTemplate::query()->with('createdBy');
    }

    /**
     * `query_data`, `columns` and `filters` are the template's own definition —
     * JSON describing how it builds a report. They are implementation, not an
     * axis anyone browses templates along, so they stay out.
     *
     * @return array<string, Filter>
     */
    protected function allowedFilters(): array
    {
        return [
            'category' => EnumFilter::make(ReportCategory::class)->label('report.templates.category'),
            'is_active' => BooleanFilter::make()->label('report.templates.active'),
            'name' => TranslatableFilter::make()->label('report.templates.name'),
            'code' => TextFilter::make()->label('report.templates.code'),
            'created_by' => ExactFilter::make()->label('report.templates.author')->nullable(),
        ];
    }
}
