<?php

namespace Modules\SchoolClass\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\SchoolClass\Models\SchoolClass;

class ClassQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $searchableFields = ['name'];

    /** @var array<int, string> */
    protected array $filterableFields = ['academic_year_id', 'grade_level_id', 'is_active'];

    /** @var array<int, string> */
    protected array $sortableFields = [
        'name',
        'capacity',
        'is_active',
        'created_at',
    ];

    protected function baseQuery(): Builder
    {
        return SchoolClass::query()->with(['gradeLevel', 'academicYear', 'classroom']);
    }

    public function academicYearId(?int $academicYearId): self
    {
        if ($academicYearId) {
            $this->query->where('academic_year_id', $academicYearId);
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

    public function isActive(?bool $isActive): self
    {
        if ($isActive !== null) {
            $this->query->where('is_active', $isActive);
        }

        return $this;
    }
}
