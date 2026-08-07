<?php

namespace Modules\Finance\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Finance\Models\FeeDiscount;

class FeeDiscountQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $searchableFields = ['name', 'code'];

    /** @var array<int, string> */
    protected array $filterableFields = ['discount_type', 'grade_level_id', 'classroom_id', 'is_active'];

    /** @var array<int, string> */
    protected array $sortableFields = ['discount_value', 'start_date', 'end_date', 'is_active', 'created_at'];

    protected function baseQuery(): Builder
    {
        return FeeDiscount::query()->with(['gradeLevel', 'classroom']);
    }

    public function searchTerm(?string $term): self
    {
        if (! $term) {
            return $this;
        }

        $this->query->where(function (Builder $query) use ($term) {
            $query->where('name', 'LIKE', "%{$term}%")
                ->orWhere('code', 'LIKE', "%{$term}%");
        });

        return $this;
    }

    public function discountType(?string $discountType): self
    {
        if ($discountType) {
            $this->query->where('discount_type', $discountType);
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

    public function isActive(?bool $isActive): self
    {
        if ($isActive !== null) {
            $this->query->where('is_active', $isActive);
        }

        return $this;
    }
}
