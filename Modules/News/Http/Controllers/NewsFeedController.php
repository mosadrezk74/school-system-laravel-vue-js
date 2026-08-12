<?php

namespace Modules\News\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Auth\Enums\UserRole;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\News\Models\News;
use Modules\News\Queries\NewsQuery;
use Modules\News\Transformers\NewsResource;

/**
 * The news feed every non-admin reads.
 *
 * One controller and one query serve students, guardians and teachers — the
 * audience filter is the only difference between them, and it is derived from
 * the signed-in user's own account type, never from the request. Staff are not
 * filtered by audience, so an admin previewing the feed sees the lot.
 */
class NewsFeedController extends BaseInertiaController
{
    public function index(Request $request): Response
    {
        $this->authorize('viewFeed', News::class);

        $news = (new NewsQuery)
            ->feedFor($this->audienceRoleFor($request))
            ->paginate($request->integer(Pagination::PER_PAGE_PARAM, Pagination::DEFAULT_PER_PAGE));

        return $this->render('News::News/Feed', [
            'news' => NewsResource::collection($news),
        ]);
    }

    /**
     * Staff read everything, so they filter by no audience at all. Everyone
     * else is pinned to their own account type.
     */
    private function audienceRoleFor(Request $request): ?UserRole
    {
        $role = $request->user()?->role;

        return in_array($role, [UserRole::Owner, UserRole::Admin], true) ? null : $role;
    }
}
