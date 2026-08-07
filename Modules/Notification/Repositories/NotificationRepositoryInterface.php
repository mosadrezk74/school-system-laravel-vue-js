<?php

namespace Modules\Notification\Repositories;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface NotificationRepositoryInterface
{
    public function paginateFor(User $user, ?bool $unreadOnly, int $perPage): LengthAwarePaginator;

    public function unreadCountFor(User $user): int;

    public function markAsRead(User $user, string $notificationId): bool;

    public function markAllAsRead(User $user): int;
}
