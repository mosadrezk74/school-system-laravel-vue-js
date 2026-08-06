<?php

namespace Modules\Examination\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Examination\Models\Exam;

class ExamQuery extends BaseQuery
{
    /**
     * `name` is now a Spatie-translatable JSON column, so a plain LIKE
     * against it matches raw JSON rather than the human-readable text (same
     * tradeoff already accepted by BehaviorCategoryQuery). Searches `code`
     * only rather than over-engineering a JSON-aware search.
     *
     * @var array<int, string>
     */
    protected array $searchableFields = ['code'];

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
