<?php

namespace Modules\StudyMaterial\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\StudyMaterial\Models\StudyResource;

class StudyResourceQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $searchableFields = ['title'];

    /** @var array<int, string> */
    protected array $filterableFields = ['subject_id', 'grade_level_id', 'teacher_id', 'type'];

    /** @var array<int, string> */
    protected array $sortableFields = [
        'type',
        'downloads_count',
        'created_at',
    ];

    protected function baseQuery(): Builder
    {
        return StudyResource::query()->with(['subject', 'gradeLevel', 'classroom', 'teacher', 'media']);
    }

    public function searchTitle(?string $term): self
    {
        if (! $term) {
            return $this;
        }

        $this->query->where('title', 'LIKE', "%{$term}%");

        return $this;
    }

    public function subjectId(?int $subjectId): self
    {
        if ($subjectId) {
            $this->query->where('subject_id', $subjectId);
        }

        return $this;
    }

    public function gradeLevelId(?int $gradeLevelId): self
    {
        if ($gradeLevelId) {
            $this->query->where('grade_level_id', $gradeLevelId);
        }

        return $this;
    }

    /**
     * Restricts to a fixed set of grade levels. An empty array means "none",
     * never "unfiltered", so a portal user with no classes sees nothing.
     *
     * @param  array<int, int>|null  $gradeLevelIds
     */
    public function gradeLevelIds(?array $gradeLevelIds): self
    {
        if ($gradeLevelIds === null) {
            return $this;
        }

        $gradeLevelIds === []
            ? $this->query->whereRaw('1 = 0')
            : $this->query->whereIn('grade_level_id', $gradeLevelIds);

        return $this;
    }

    public function teacherId(?int $teacherId): self
    {
        if ($teacherId) {
            $this->query->where('teacher_id', $teacherId);
        }

        return $this;
    }

    public function type(?string $type): self
    {
        if ($type) {
            $this->query->where('type', $type);
        }

        return $this;
    }

    public function ownedBy(?int $teacherId): self
    {
        if ($teacherId) {
            $this->query->where('teacher_id', $teacherId);
        }

        return $this;
    }
}
