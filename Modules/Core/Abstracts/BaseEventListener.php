<?php

namespace Modules\Core\Abstracts;

use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Business-event listeners (Order/Service/Wallet/etc.) are almost always
 * dispatched from inside a DB transaction (see BaseAction::executeWithTransaction).
 * A sync listener would run immediately, before the transaction commits -
 * a Mail/Notification could go out for a refund/order change that then
 * rolls back. Queuing the listener with $afterCommit defers it until the
 * transaction actually commits, matching Laravel's native afterCommit
 * support for queued listeners.
 */
abstract class BaseEventListener implements ShouldQueue
{
    public bool $afterCommit = true;

    public string $connection = 'redis';

    public string $queue = 'notifications-db';

    public int $tries = 3;

    public array $backoff = [10, 60, 300];
}
