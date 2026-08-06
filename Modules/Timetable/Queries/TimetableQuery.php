<?php

namespace Modules\Timetable\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Timetable\Models\Timetable;

class TimetableQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $filterableFields = ['class_id', 'academic_year_id', 'semester_id', 'is_active'];

    /** @var array<int, string> */
    protected array $sortableFields = ['created_at'];

    protected function baseQuery(): Builder
    {
        return Timetable::query()->with(['class.gradeLevel', 'class.branch', 'academicYear', 'semester']);
    }

    public function classId(?int $classId): self
    {
        if ($classId) {
            $this->query->where('class_id', $classId);
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

    public function isActive(?bool $isActive): self
    {
        if ($isActive !== null) {
            $this->query->where('is_active', $isActive);
        }

        return $this;
    }
}
