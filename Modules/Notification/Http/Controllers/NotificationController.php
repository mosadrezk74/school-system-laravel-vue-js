<?php

namespace Modules\Notification\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\Notification\Repositories\NotificationRepositoryInterface;
use Modules\Notification\Transformers\NotificationResource;

class NotificationController extends BaseInertiaController
{
    public function __construct(private readonly NotificationRepositoryInterface $repository) {}

    public function index(Request $request): Response
    {
        $user = $request->user();

        $notifications = $this->repository->paginateFor(
            $user,
            $request->boolean('unread') ?: null,
            $request->integer(Pagination::PER_PAGE_PARAM, Pagination::DEFAULT_PER_PAGE),
        );

        return $this->render('Notification::Notifications/Index', [
            'notifications' => NotificationResource::collection($notifications),
            'unreadCount' => $this->repository->unreadCountFor($user),
            'filters' => ['unread' => $request->boolean('unread')],
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
