<?php

namespace Modules\Portal\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\Meeting\Models\Meeting;
use Modules\Meeting\Queries\MeetingQuery;
use Modules\Meeting\Transformers\MeetingResource;
use Modules\Portal\Services\PortalScopeService;

class StudentMeetingController extends BaseInertiaController
{
    public function __construct(private readonly PortalScopeService $scope) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Meeting::class);

        $meetings = (new MeetingQuery)
            ->classIds($this->scope->accessibleClassIds())
            ->search($request->string(Pagination::SEARCH_PARAM)->toString() ?: null)
            ->status($request->string('status')->toString() ?: null)
            ->sort('scheduled_at', 'desc')
            ->paginate($request->integer(Pagination::PER_PAGE_PARAM, Pagination::DEFAULT_PER_PAGE));

        return $this->render('Portal::Student/Meetings', [
            'meetings' => MeetingResource::collection($meetings),
            'filters' => $request->only([Pagination::SEARCH_PARAM, 'status']),
        ]);
    }
}
