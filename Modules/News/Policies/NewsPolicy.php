<?php

namespace Modules\News\Policies;

use App\Models\User;
use Modules\News\Models\News;

/**
 * Writing news is an administrative act; reading the feed is open to everyone.
 *
 * `viewAny` guards the admin browser, which lists drafts and every audience,
 * so it needs the permission. The portal feed uses `viewFeed` instead — the
 * audience and publish filtering is what limits what a reader gets back, not a
 * permission they would otherwise have to be granted.
 */
class NewsPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('news.view');
    }

    public function view(User $user, News $news): bool
    {
        return $user->can('news.view');
    }

    public function create(User $user): bool
    {
        return $user->can('news.create');
    }

    public function update(User $user, News $news): bool
    {
        return $user->can('news.update');
    }

    public function delete(User $user, News $news): bool
    {
        return $user->can('news.delete');
    }

    public function restore(User $user, News $news): bool
    {
        return $user->can('news.delete');
    }

    /** Publishing decides what the whole school sees, so it is its own grant. */
    public function publish(User $user, News $news): bool
    {
        return $user->can('news.update');
    }

    /**
     * Any signed-in, active account may read the feed. What it contains is
     * decided by NewsQuery::feedFor(), not here.
     */
    public function viewFeed(User $user): bool
    {
        return $user->isActive();
    }
}
