<?php

namespace Modules\Teacher\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Teacher\Models\TeacherProfile;

class TeacherQuery extends BaseQuery
{
    protected array $searchableFields = ['employee_id', 'qualification', 'specialization', 'position'];

    protected array $filterableFields = ['is_active'];

    protected array $sortableFields = [
        'employee_id',
        'position',
        'experience_years',
        'joining_date',
        'is_active',
        'created_at',
    ];

    protected function baseQuery(): Builder
    {
        return TeacherProfile::query()->with(['user', 'subjects']);
    }

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

    public function isActive(?bool $isActive): self
    {
        if ($isActive !== null) {
            $this->query->where('is_active', $isActive);
        }

        return $this;
    }

    public function subjectId(?int $subjectId): self
    {
        if ($subjectId) {
            $this->query->whereHas('subjects', function (Builder $query) use ($subjectId) {
                $query->where('subjects.id', $subjectId);
            });
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
