<?php

namespace Modules\Classroom\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Classroom\Models\Classroom;
use Modules\Core\Abstracts\BaseQuery;

class ClassroomQuery extends BaseQuery
{
    protected array $searchableFields = ['name', 'code', 'building'];

    protected array $filterableFields = ['branch_id', 'room_type', 'is_active'];

    protected array $sortableFields = ['name', 'code', 'capacity', 'floor', 'building', 'room_type', 'is_active', 'created_at'];

    protected function baseQuery(): Builder
    {
        return Classroom::query()->with(['branch']);
    }

    public function search(?string $term): self
    {
        if (!$term) {
            return $this;
        }

        $this->query->where(function (Builder $query) use ($term) {
            foreach ($this->searchableFields as $field) {
                $query->orWhere($field, 'LIKE', "%{$term}%");
            }
        });

        return $this;
    }

    public function branchId(?int $branchId): self
    {
        if ($branchId) {
            $this->query->where('branch_id', $branchId);
        }

        return $this;
    }

    public function roomType(?string $roomType): self
    {
        if ($roomType) {
            $this->query->where('room_type', $roomType);
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
