<?php

namespace Modules\Student\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Student\Models\StudentProfile;

class StudentQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $searchableFields = ['admission_number', 'roll_number'];

    /** @var array<int, string> */
    protected array $filterableFields = ['academic_year_id', 'current_class_id', 'is_active'];

    /** @var array<int, string> */
    protected array $sortableFields = [
        'admission_number',
        'roll_number',
        'admission_date',
        'is_active',
        'created_at',
    ];

    protected function baseQuery(): Builder
    {
        return StudentProfile::query()->with(['user', 'academicYear', 'currentClass']);
    }

    /**
     * BaseQuery's search() only matches columns on student_profiles itself;
     * name/email/username live on the related user, so this searches both.
     */
    public function searchUser(?string $term): self
    {
        if (! $term) {
            return $this;
        }

        $this->query->where(function (Builder $query) use ($term) {
            foreach ($this->searchableFields as $field) {
                $query->orWhere($field, 'LIKE', "%{$term}%");
            }

            $query->orWhereHas('user', function (Builder $userQuery) use ($term) {
                $userQuery->where('name', 'LIKE', "%{$term}%")
                    ->orWhere('email', 'LIKE', "%{$term}%")
                    ->orWhere('username', 'LIKE', "%{$term}%");
            });
        });

        return $this;
    }

    public function academicYearId(?int $academicYearId): self
    {
        if ($academicYearId) {
            $this->query->where('academic_year_id', $academicYearId);
        }

        return $this;
    }

    public function currentClassId(?int $currentClassId): self
    {
        if ($currentClassId) {
            $this->query->where('current_class_id', $currentClassId);
        }

        return $this;
    }

    public function gradeLevelId(?int $gradeLevelId): self
    {
        if ($gradeLevelId) {
            $this->query->whereHas('currentClass', function (Builder $query) use ($gradeLevelId) {
                $query->where('grade_level_id', $gradeLevelId);
            });
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

    public function gender(?string $gender): self
    {
        if ($gender) {
            $this->query->whereHas('user', function (Builder $query) use ($gender) {
                $query->where('gender', $gender);
            });
        }

        return $this;
    }
}
