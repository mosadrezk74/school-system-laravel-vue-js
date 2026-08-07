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
use Modules\Portal\Traits\ResolvesGuardianChildren;

class ParentMeetingController extends BaseInertiaController
{
    use ResolvesGuardianChildren;

    public function __construct(private readonly PortalScopeService $scope) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Meeting::class);

        $child = $this->selectedChild($request);

        $meetings = (new MeetingQuery)
            ->classIds(array_values(array_filter([$child?->current_class_id])))
            ->sort('scheduled_at', 'desc')
            ->paginate($request->integer(Pagination::PER_PAGE_PARAM, Pagination::DEFAULT_PER_PAGE));

        return $this->render('Portal::Parent/Meetings', [
            'meetings' => MeetingResource::collection($meetings),
            'children' => $this->childOptions(),
            'selectedChild' => $child?->id,
        ]);
    }
}
