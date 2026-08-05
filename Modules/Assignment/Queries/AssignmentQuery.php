<?php

namespace Modules\Assignment\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Assignment\Models\Assignment;
use Modules\Core\Abstracts\BaseQuery;

class AssignmentQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $searchableFields = ['title'];

    /** @var array<int, string> */
    protected array $filterableFields = [
        'class_id', 'subject_id', 'teacher_id', 'academic_year_id', 'semester_id', 'is_published',
    ];

    /** @var array<int, string> */
    protected array $sortableFields = ['title', 'deadline', 'max_score', 'created_at'];

    protected function baseQuery(): Builder
    {
        return Assignment::query()->with(['class', 'subject', 'teacher', 'academicYear', 'semester']);
    }

    /**
     * BaseQuery::search() applies its where() unconditionally, which would
     * turn an empty term into a no-op `LIKE '%%'` clause; guarding here keeps
     * the query plan clean when no search term was submitted.
     */
    public function searchTitle(?string $term): self
    {
        if ($term) {
            $this->search($term);
        }

        return $this;
    }

    public function classId(?int $classId): self
    {
        if ($classId) {
            $this->query->where('class_id', $classId);
        }

        return $this;
    }

    public function subjectId(?int $subjectId): self
    {
        if ($subjectId) {
            $this->query->where('subject_id', $subjectId);
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

    public function academicYearId(?int $academicYearId): self
    {
        if ($academicYearId) {
            $this->query->where('academic_year_id', $academicYearId);
        }

        return $this;
    }

    public function semesterId(?int $semesterId): self
    {
        if ($semesterId) {
            $this->query->where('semester_id', $semesterId);
        }

        return $this;
    }

    public function isPublished(?bool $isPublished): self
    {
        if ($isPublished !== null) {
            $this->query->where('is_published', $isPublished);
        }

        return $this;
    }
}
