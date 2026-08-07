<?php

namespace Modules\Notification\Queries;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Notification\Models\Notification;

class NotificationQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $filterableFields = ['type', 'read_at'];

    /** @var array<int, string> */
    protected array $sortableFields = ['created_at', 'read_at', 'type'];

    protected function baseQuery(): Builder
    {
        return Notification::query();
    }

    /**
     * Constrains the query to one notifiable. An unset owner yields nothing
     * rather than everything, so a missing scope can never leak.
     */
    public function forUser(?User $user): self
    {
        if (! $user instanceof User) {
            $this->query->whereRaw('1 = 0');

            return $this;
        }

        $this->query
            ->where('notifiable_type', $user->getMorphClass())
            ->where('notifiable_id', $user->getKey());

        return $this;
    }

    public function unreadOnly(?bool $unreadOnly): self
    {
        if ($unreadOnly) {
            $this->query->whereNull('read_at');
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
