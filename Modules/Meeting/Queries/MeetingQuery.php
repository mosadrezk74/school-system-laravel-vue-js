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
