<?php

namespace Modules\Communication\Services;

use Illuminate\Support\Facades\Notification as NotificationFacade;
use Modules\Communication\Models\BroadcastMessage;
use Modules\Communication\Notifications\InternalMessageNotification;
use Modules\Communication\Repositories\MessageRepositoryInterface;

class MessageDispatcherService
{
    private const CHUNK_SIZE = 200;

    public function __construct(
        private readonly MessageRepositoryInterface $repository,
        private readonly RecipientResolverService $resolver,
    ) {}

    /**
     * Returns as soon as the response is flushed; delivery to a large
     * audience then runs without holding the request open.
     */
    public function dispatch(BroadcastMessage $message): void
    {
        dispatch(fn () => $this->deliver($message))->afterResponse();
    }

    public function deliver(BroadcastMessage $message): void
    {
        $this->resolver->resolve($message->target_type, $message->target_ids)
            ->select(['id', 'language'])
            ->chunkById(self::CHUNK_SIZE, function ($users) use ($message): void {
                NotificationFacade::send($users, new InternalMessageNotification($message));
            });

        $this->repository->markSent($message);
    }
}
