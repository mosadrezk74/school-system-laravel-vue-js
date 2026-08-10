<?php

namespace Modules\Examination\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Examination\Models\Exam;

class ExamQuery extends BaseQuery
{
    /**
     * `name` is a Spatie-translatable JSON column. A LIKE against it matches
     * the stored JSON, which still finds the typed text for any locale — it
     * is the only searchable column left now that `code` is gone.
     *
     * @var array<int, string>
     */
    protected array $searchableFields = ['name'];

    /** @var array<int, string> */
    protected array $filterableFields = ['academic_year_id', 'semester_id', 'class_id', 'exam_type', 'is_published'];

    /** @var array<int, string> */
    protected array $sortableFields = ['start_date', 'end_date', 'created_at'];

    protected function baseQuery(): Builder
    {
        return Exam::query()->with(['academicYear', 'semester', 'class']);
    }

    public function searchNameOrCode(?string $term): self
    {
        if ($term) {
            $this->search($term);
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

    public function classId(?int $classId): self
    {
        if ($classId) {
            $this->query->where('class_id', $classId);
        }

        return $this;
    }

    public function examType(?string $examType): self
    {
        if ($examType) {
            $this->query->where('exam_type', $examType);
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
