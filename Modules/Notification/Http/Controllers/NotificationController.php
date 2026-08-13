<?php

namespace Modules\Notification\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\DTOs\ListQueryDTO;
use Modules\Notification\Queries\NotificationQuery;
use Modules\Notification\Repositories\NotificationRepositoryInterface;
use Modules\Notification\Transformers\NotificationResource;

class NotificationController extends BaseInertiaController
{
    public function __construct(private readonly NotificationRepositoryInterface $repository) {}

    public function index(Request $request): Response
    {
        $user = $request->user();

        $filters = ListQueryDTO::fromRequest($request);

        /*
         * forUser() is the ownership scope and is applied to the query itself,
         * so no request parameter can reach another account's notifications.
         * `?unread=1` keeps working alongside the schema's `is_read` control.
         */
        $query = (new NotificationQuery)
            ->forUser($user)
            ->unreadOnly($request->boolean('unread') ?: null);

        $notifications = $query
            ->where($filters->values)
            ->sort($filters->sortBy, $filters->sortOrder)
            ->paginate($filters->perPage);

        return $this->render('Notification::Notifications/Index', [
            'notifications' => NotificationResource::collection($notifications),
            'unreadCount' => $this->repository->unreadCountFor($user),
            'filters' => [
                'unread' => $request->boolean('unread'),
                ...$query->activeFilters($filters->values),
            ],
            'filterDefinitions' => $query->filterSchema(),
        ]);
    }

    public function markAsRead(Request $request, string $notification): RedirectResponse
    {
        $this->repository->markAsRead($request->user(), $notification);

        return $this->backWithSuccess(__('notification::notification.marked_read'));
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        $this->repository->markAllAsRead($request->user());

        return $this->backWithSuccess(__('notification::notification.marked_all_read'));
    }
}
