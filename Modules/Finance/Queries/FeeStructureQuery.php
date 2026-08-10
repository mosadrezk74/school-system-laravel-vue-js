<?php

namespace Modules\Finance\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Finance\Models\FeeStructure;

class FeeStructureQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $searchableFields = ['name'];

    /** @var array<int, string> */
    protected array $filterableFields = ['fee_category_id', 'grade_level_id', 'classroom_id', 'frequency'];

    /** @var array<int, string> */
    protected array $sortableFields = ['amount', 'frequency', 'due_day', 'created_at'];

    protected function baseQuery(): Builder
    {
        return FeeStructure::query()->with(['feeCategory', 'gradeLevel', 'classroom', 'academicYear', 'semester']);
    }

    public function searchName(?string $term): self
    {
        if ($term) {
            $this->query->where('name', 'LIKE', "%{$term}%");
        }

        return $this;
    }

    public function feeCategoryId(?int $feeCategoryId): self
    {
        if ($feeCategoryId) {
            $this->query->where('fee_category_id', $feeCategoryId);
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

    public function classroomId(?int $classroomId): self
    {
        if ($classroomId) {
            $this->query->where('classroom_id', $classroomId);
        }

        return $this;
    }

    public function frequency(?string $frequency): self
    {
        if ($frequency) {
            $this->query->where('frequency', $frequency);
        }

        return $this;
    }

    public function isOptional(?bool $isOptional): self
    {
        if ($isOptional !== null) {
            $this->query->where('is_optional', $isOptional);
        }

        return $this;
    }
}
