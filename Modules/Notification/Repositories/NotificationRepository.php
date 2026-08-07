<?php

namespace Modules\Notification\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Notification\Models\Notification;

/**
 * Data access only. Reads through the standard Laravel notifications table so
 * anything dispatched with Laravel's own Notification facade shows up here
 * without a second storage mechanism.
 */
class NotificationRepository implements NotificationRepositoryInterface
{
    public function paginateFor(User $user, ?bool $unreadOnly, int $perPage): LengthAwarePaginator
    {
        return $this->scopedTo($user)
            ->when($unreadOnly, fn ($query) => $query->whereNull('read_at'))
            ->latest('created_at')
            ->paginate($perPage);
    }

    public function unreadCountFor(User $user): int
    {
        return $this->scopedTo($user)->whereNull('read_at')->count();
    }

    public function markAsRead(User $user, string $notificationId): bool
    {
        return (bool) $this->scopedTo($user)
            ->whereKey($notificationId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function markAllAsRead(User $user): int
    {
        return $this->scopedTo($user)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Every read and write is constrained to the signed-in notifiable, so a
     * notification id from the request can never reach another user's row.
     *
     * @return Builder<Notification>
     */
    private function scopedTo(User $user)
    {
        return Notification::query()
            ->where('notifiable_type', $user->getMorphClass())
            ->where('notifiable_id', $user->getKey());
    }
}
