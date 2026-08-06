<?php

namespace Modules\Examination\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Examination\Models\ExamGrade;

class ExamGradeQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $searchableFields = ['grade_name'];

    /** @var array<int, string> */
    protected array $filterableFields = ['grade_level_id', 'subject_id'];

    /** @var array<int, string> */
    protected array $sortableFields = ['grade_name', 'min_percentage', 'created_at'];

    protected function baseQuery(): Builder
    {
        return ExamGrade::query()->with(['gradeLevel', 'subject']);
    }

    public function searchGradeName(?string $term): self
    {
        if ($term) {
            $this->search($term);
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

    public function subjectId(?int $subjectId): self
    {
        if ($subjectId) {
            $this->query->where('subject_id', $subjectId);
        }

        return $this;
    }
}
