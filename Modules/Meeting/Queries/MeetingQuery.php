<?php

namespace Modules\Meeting\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Core\Support\Filters\DateFilter;
use Modules\Core\Support\Filters\EnumFilter;
use Modules\Core\Support\Filters\ExactFilter;
use Modules\Core\Support\Filters\Filter;
use Modules\Core\Support\Filters\NumericFilter;
use Modules\Meeting\Enums\MeetingStatus;
use Modules\Meeting\Models\Meeting;

class MeetingQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $searchableFields = ['title', 'room_name'];

    /** @var array<string, array<int, string>> */
    protected array $searchableRelations = [
        'teacher' => ['name'],
    ];

    /** @var array<int, string> */
    protected array $sortableFields = ['scheduled_at', 'title', 'status', 'created_at'];

    protected string $defaultSortField = 'scheduled_at';

    protected function baseQuery(): Builder
    {
        return Meeting::query()->with(['teacher', 'academicYear', 'semester', 'subject', 'schoolClass']);
    }

    /**
     * `uuid`, `room_name` and `meeting_url` are the conferencing plumbing: the
     * room name is searchable because staff quote it, but none of the three is
     * an axis anyone lists meetings along.
     *
     * `status` is whitelisted by MeetingStatus. The four values used to exist
     * only in the Vue dropdown, so nothing on the server constrained what
     * `?status=` could be.
     *
     * @return array<string, Filter>
     */
    protected function allowedFilters(): array
    {
        return [
            'teacher_id' => ExactFilter::make()->label('meetings.teacher'),
            'status' => EnumFilter::make(MeetingStatus::class)->label('meetings.status'),
            'subject_id' => ExactFilter::make()->label('meetings.subject')->nullable(),
            'school_class_id' => ExactFilter::make()->label('meetings.class')->nullable(),
            'academic_year_id' => ExactFilter::make()->label('meetings.academicYear')->nullable(),
            'semester_id' => ExactFilter::make()->label('meetings.semester')->nullable(),

            // `scheduled_at` is a timestamp; DateFilter compares by day, which
            // is what the existing `?date=` parameter has always meant.
            'date' => DateFilter::make()->column('scheduled_at')->label('meetings.date'),

            'duration_minutes' => NumericFilter::make()->label('meetings.duration'),
        ];
    }

    /**
     * Role scoping, not a user-facing filter: a teacher sees only their own
     * meetings. Applied to the query itself so no request parameter widens it.
     */
    public function teacherId(?int $teacherId): self
    {
        if ($teacherId) {
            $this->query->where('teacher_id', $teacherId);
        }

        return $this;
    }

    /** Role scoping — a student is pinned to live meetings. */
    public function status(?string $status): self
    {
        if ($status) {
            $this->query->where('status', $status);
        }

        return $this;
    }

    public function upcomingOnly(?bool $upcoming): self
    {
        if ($upcoming) {
            $this->query->where('scheduled_at', '>=', now());
        }

        return $this;
    }

    /**
     * Restricts to a fixed set of classes — portal scoping. An empty array
     * means "none", never "unfiltered".
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
            : $this->query->whereIn('school_class_id', $classIds);

        return $this;
    }
}
