<?php

namespace Modules\Assignment\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Assignment\Enums\AssignmentType;
use Modules\Assignment\Models\Assignment;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Core\Support\Filters\BooleanFilter;
use Modules\Core\Support\Filters\DateRangeFilter;
use Modules\Core\Support\Filters\EnumFilter;
use Modules\Core\Support\Filters\ExactFilter;
use Modules\Core\Support\Filters\Filter;
use Modules\Core\Support\Filters\NumericFilter;
use Modules\Core\Support\Filters\TranslatableFilter;

class AssignmentQuery extends BaseQuery
{
    /**
     * `title` and `description` became translatable JSON columns.
     *
     * @var array<int, string>
     */
    protected array $translatableSearchFields = ['title', 'description'];

    /** @var array<int, string> */
    protected array $sortableFields = ['deadline', 'max_score', 'published_at', 'created_at'];

    protected string $defaultSortField = 'deadline';

    protected function baseQuery(): Builder
    {
        return Assignment::query()->with(['class', 'subject', 'teacher', 'academicYear', 'semester']);
    }

    /**
     * The academic year is a real column here — an assignment is set in a
     * specific year — so it stays an explicit historical filter rather than
     * being substituted with the current context.
     *
     * @return array<string, Filter>
     */
    protected function allowedFilters(): array
    {
        return [
            'class_id' => ExactFilter::make()->label('assignments.class'),
            'subject_id' => ExactFilter::make()->label('assignments.subject'),
            'teacher_id' => ExactFilter::make()->label('assignments.teacher'),
            'academic_year_id' => ExactFilter::make()->label('assignments.academicYear'),
            'semester_id' => ExactFilter::make()->label('assignments.semester')->nullable(),
            'assignment_type' => EnumFilter::make(AssignmentType::class)->label('assignments.type'),
            'is_published' => BooleanFilter::make()->label('assignments.published'),
            'is_group_work' => BooleanFilter::make()->label('assignments.groupWork'),
            'deadline' => DateRangeFilter::make()->label('assignments.deadline'),
            'max_score' => NumericFilter::make()->label('assignments.maxScore'),
            'title' => TranslatableFilter::make()->label('assignments.title'),
        ];
    }

    /**
     * Restricts to a fixed set of classes. An empty array means "none", never
     * "unfiltered" — portal scoping, so it stays a method rather than joining
     * allowedFilters().
     *
     * @param  array<int, int>|null  $classIds
     */
    public function classIds(?array $classIds): self
    {
        if ($classIds === null) {
            return $this;
        }

        $classIds === []
            ? $this->query->whereRaw('1 = 0')
            : $this->query->whereIn('class_id', $classIds);

        return $this;
    }
}
