<?php

namespace Modules\Notification\Queries;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Core\Support\Filters\DateRangeFilter;
use Modules\Core\Support\Filters\Filter;
use Modules\Core\Support\Filters\TextFilter;
use Modules\Notification\Models\Notification;

class NotificationQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $sortableFields = ['created_at', 'read_at', 'type'];

    protected function baseQuery(): Builder
    {
        return Notification::query();
    }

    /**
     * `notifiable_type` / `notifiable_id` are the ownership columns, set by
     * forUser() and never filterable — letting them through would be a way to
     * read another account's notifications.
     *
     * `data` is the rendered payload, read per-notification rather than
     * narrowed across a list.
     *
     * Read state is deliberately absent: the page's existing `?unread=1`
     * toggle already expresses it through unreadOnly(), and adding a second
     * control for the same thing is exactly the duplication this system is
     * meant to remove.
     *
     * @return array<string, Filter>
     */
    protected function allowedFilters(): array
    {
        return [
            'type' => TextFilter::make()->label('notification.type'),
            'created_at' => DateRangeFilter::make()->label('notification.date'),
        ];
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
}
