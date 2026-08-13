<?php

namespace Modules\Promotion\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Core\Support\Filters\BooleanFilter;
use Modules\Core\Support\Filters\DateRangeFilter;
use Modules\Core\Support\Filters\EnumFilter;
use Modules\Core\Support\Filters\ExactFilter;
use Modules\Core\Support\Filters\Filter;
use Modules\Promotion\Enums\PromotionStatus;
use Modules\Promotion\Models\StudentPromotion;

class PromotionQuery extends BaseQuery
{
    /** Student names live on users, so the search reaches through the profile. */
    protected array $searchableRelations = [
        'student.user' => ['name'],
    ];

    /** @var array<int, string> */
    protected array $sortableFields = ['promoted_at', 'status', 'created_at'];

    protected string $defaultSortField = 'promoted_at';

    protected function baseQuery(): Builder
    {
        return StudentPromotion::query()->with([
            'student.user',
            'fromAcademicYear',
            'toAcademicYear',
            'toSemester',
            'fromClass',
            'toClass',
            'promotedBy',
        ]);
    }

    /**
     * Source and target years are both filterable and both historical: a
     * promotion is precisely the record of moving between two years, so
     * neither is ever substituted with the current context.
     *
     * `remarks` is free text staff write when overriding a decision — searched
     * rather than filtered.
     *
     * @return array<string, Filter>
     */
    protected function allowedFilters(): array
    {
        return [
            'status' => EnumFilter::make(PromotionStatus::class)->label('promotions.status'),
            'student_id' => ExactFilter::make()->label('promotions.student'),
            'from_academic_year_id' => ExactFilter::make()->label('promotions.fromYear'),
            'to_academic_year_id' => ExactFilter::make()->label('promotions.toYear')->nullable(),
            'from_class_id' => ExactFilter::make()->label('promotions.fromClass')->nullable(),
            'to_class_id' => ExactFilter::make()->label('promotions.toClass')->nullable(),
            'was_graduated' => BooleanFilter::make()->label('promotions.wasGraduated'),
            'promoted_by' => ExactFilter::make()->label('promotions.promotedBy')->nullable(),
            'promoted' => DateRangeFilter::make()->column('promoted_at')->label('promotions.promotedAt'),
        ];
    }

    public function latestFirst(): self
    {
        $this->query->latest('promoted_at');

        return $this;
    }
}
