<?php

namespace Modules\Finance\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Finance\Models\PaymentMethod;

class PaymentMethodQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $searchableFields = ['code', 'name'];

    /** @var array<int, string> */
    protected array $filterableFields = ['is_active', 'type'];

    /** @var array<int, string> */
    protected array $sortableFields = ['code', 'type', 'is_active', 'display_order', 'created_at'];

    protected function baseQuery(): Builder
    {
        return PaymentMethod::query();
    }

    public function searchTerm(?string $term): self
    {
        if (! $term) {
            return $this;
        }

        $this->query->where(function (Builder $query) use ($term) {
            $query->where('code', 'LIKE', "%{$term}%")
                ->orWhere('name', 'LIKE', "%{$term}%");
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

    public function type(?string $type): self
    {
        if ($type) {
            $this->query->where('type', $type);
        }

        return $this;
    }
}
