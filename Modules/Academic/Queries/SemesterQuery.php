<?php

namespace Modules\Academic\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Academic\Models\Semester;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Core\Support\Filters\BooleanFilter;
use Modules\Core\Support\Filters\DateRangeFilter;
use Modules\Core\Support\Filters\ExactFilter;
use Modules\Core\Support\Filters\Filter;
use Modules\Core\Support\Filters\NumericFilter;
use Modules\Core\Support\Filters\TranslatableFilter;

class SemesterQuery extends BaseQuery
{
    /**
     * `name` is JSON keyed by locale, matched through each enabled language.
     *
     * @var array<int, string>
     */
    protected array $translatableSearchFields = ['name'];

    /** @var array<int, string> */
    protected array $sortableFields = [
        'sequence',
        'start_date',
        'end_date',
        'is_active',
        'created_at',
    ];

    protected function baseQuery(): Builder
    {
        return Semester::query()->with('academicYear:id,name');
    }

    /**
     * A semester genuinely belongs to an academic year — the column is on the
     * table — so the year is an explicit historical filter, never substituted
     * with the current context.
     *
     * @return array<string, Filter>
     */
    protected function allowedFilters(): array
    {
        return [
            'academic_year_id' => ExactFilter::make()->label('semesters.academicYear'),
            'is_active' => BooleanFilter::make()->label('semesters.isActive'),
            'is_current' => BooleanFilter::make()->label('semesters.isCurrent'),
            'name' => TranslatableFilter::make()->label('semesters.name'),
            'sequence' => NumericFilter::make()->label('semesters.sequence'),
            'start_date' => DateRangeFilter::make()->label('semesters.startDate'),
            'end_date' => DateRangeFilter::make()->label('semesters.endDate'),
            'exam_start_date' => DateRangeFilter::make()->label('semesters.examStartDate'),
        ];
    }

    /** Archived rows are listed alongside live ones so they can be restored. */
    public function withArchived(): self
    {
        $this->query->withTrashed();

        return $this;
    }
}
