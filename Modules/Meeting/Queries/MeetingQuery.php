<?php

namespace Modules\Meeting\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Meeting\Models\Meeting;

class MeetingQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $searchableFields = ['title', 'room_name'];

    /** @var array<int, string> */
    protected array $filterableFields = ['teacher_id', 'status'];

    /** @var array<int, string> */
    protected array $sortableFields = ['scheduled_at', 'title', 'created_at'];

    protected function baseQuery(): Builder
    {
        return Meeting::query()->with(['teacher', 'academicYear', 'semester', 'subject', 'schoolClass']);
    }

    public function searchTerm(?string $term): self
    {
        if ($term) {
            $this->search($term);
        }

        return $this;
    }

    public function teacherId(?int $teacherId): self
    {
        if ($teacherId) {
            $this->query->where('teacher_id', $teacherId);
        }

        return $this;
    }

    /**
     * Restricts to a fixed set of classes. An empty array means "none",
     * never "unfiltered", so a portal user with no classes sees nothing.
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

    public function upcomingOnly(?bool $upcoming): self
    {
        if ($upcoming) {
            $this->query->where('scheduled_at', '>=', now());
        }

        return $this;
    }

    public function status(?string $status): self
    {
        if ($status) {
            $this->query->where('status', $status);
        }

        return $this;
    }

    public function date(?string $date): self
    {
        if ($date) {
            $this->query->whereDate('scheduled_at', $date);
        }

        return $this;
    }
}
