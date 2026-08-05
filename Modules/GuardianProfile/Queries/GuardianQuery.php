<?php

namespace Modules\GuardianProfile\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\GuardianProfile\Models\GuardianProfile;

class GuardianQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $searchableFields = ['occupation'];

    /** @var array<int, string> */
    protected array $filterableFields = ['is_active'];

    /** @var array<int, string> */
    protected array $sortableFields = ['occupation', 'is_active', 'created_at'];

    protected function baseQuery(): Builder
    {
        return GuardianProfile::query()->with(['user']);
    }

    /**
     * BaseQuery's search() only matches columns on parent_profiles itself;
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

    public function isActive(?bool $isActive): self
    {
        if ($isActive !== null) {
            $this->query->where('is_active', $isActive);
        }

        return $this;
    }
}
