<?php

namespace Modules\Behavior\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Behavior\Enums\BehaviorRecordStatus;
use Modules\Behavior\Enums\BehaviorType;
use Modules\Behavior\Models\BehaviorRecord;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Core\Support\Filters\DateRangeFilter;
use Modules\Core\Support\Filters\EnumFilter;
use Modules\Core\Support\Filters\ExactFilter;
use Modules\Core\Support\Filters\Filter;
use Modules\Core\Support\Filters\NumericFilter;
use Modules\Core\Support\Filters\RelationFilter;

class BehaviorRecordQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $searchableFields = ['description'];

    /**
     * The record itself carries no name; a list of incidents is searched by
     * who it concerns.
     *
     * @var array<string, array<int, string>>
     */
    protected array $searchableRelations = [
        'student.user' => ['name'],
    ];

    /** @var array<int, string> */
    protected array $sortableFields = ['date', 'points', 'created_at'];

    protected string $defaultSortField = 'date';

    protected function baseQuery(): Builder
    {
        return BehaviorRecord::query()->with(['student.user', 'behaviorCategory', 'reportedBy', 'reviewedBy']);
    }

    /**
     * @return array<string, Filter>
     */
    protected function allowedFilters(): array
    {
        return [
            'student_id' => ExactFilter::make()->label('behaviorRecords.student'),
            'behavior_category_id' => ExactFilter::make()->label('behaviorRecords.category'),
            'status' => EnumFilter::make(BehaviorRecordStatus::class)->label('behaviorRecords.status'),

            // `type` lives on the category, not the record: a record is
            // positive or negative by virtue of the category it was filed under.
            'type' => EnumFilter::make(BehaviorType::class)
                ->on('behaviorCategory')
                ->column('type')
                ->label('behaviorRecords.type'),

            'date' => DateRangeFilter::make()->label('behaviorRecords.date'),
            'points' => NumericFilter::make()->label('behaviorRecords.points'),
            'reported_by' => ExactFilter::make()->label('behaviorRecords.reportedBy'),
            'is_reviewed' => RelationFilter::make('reviewedBy')->label('behaviorRecords.isReviewed'),
        ];
    }
}
