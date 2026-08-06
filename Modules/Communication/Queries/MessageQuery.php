<?php

namespace Modules\Communication\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Communication\Models\BroadcastMessage;
use Modules\Core\Abstracts\BaseQuery;

class MessageQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $searchableFields = ['title'];

    /** @var array<int, string> */
    protected array $filterableFields = ['status', 'target_type'];

    /** @var array<int, string> */
    protected array $sortableFields = ['sent_at', 'created_at'];

    protected function baseQuery(): Builder
    {
        return BroadcastMessage::query()->with(['sender']);
    }

    public function searchTerm(?string $term): self
    {
        if ($term) {
            $this->search($term);
        }

        return $this;
    }

    public function status(?string $status): self
    {
        if ($status) {
            $this->query->where('status', $status);
        }

        return $this;
    }
}
