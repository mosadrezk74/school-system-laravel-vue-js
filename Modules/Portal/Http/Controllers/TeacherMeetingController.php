<?php

namespace Modules\Portal\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\Meeting\Actions\CancelMeetingAction;
use Modules\Meeting\Actions\CreateMeetingAction;
use Modules\Meeting\Actions\FinishMeetingAction;
use Modules\Meeting\Actions\StartMeetingAction;
use Modules\Meeting\Actions\UpdateMeetingAction;
use Modules\Meeting\DTOs\CreateMeetingDTO;
use Modules\Meeting\DTOs\UpdateMeetingDTO;
use Modules\Meeting\Models\Meeting;
use Modules\Meeting\Queries\MeetingQuery;
use Modules\Meeting\Services\MeetingAccessGuard;
use Modules\Meeting\Transformers\MeetingResource;
use Modules\Portal\Http\Requests\TeacherStoreMeetingRequest;
use Modules\Portal\Http\Requests\TeacherUpdateMeetingRequest;
use Modules\Portal\Services\PortalScopeService;
use Modules\SchoolClass\Models\ClassSubject;
use Modules\SchoolClass\Models\SchoolClass;

class TeacherMeetingController extends BaseInertiaController
{
    public function __construct(private readonly PortalScopeService $scope) {}

    public function index(Request $request): Response
    {
        $meetings = (new MeetingQuery)
            ->teacherId($this->scope->teacherUserId())
            ->status($request->string('status')->toString() ?: null)
            ->search($request->string(Pagination::SEARCH_PARAM)->toString() ?: null)
            ->sort('scheduled_at', 'desc')
            ->paginate($request->integer(Pagination::PER_PAGE_PARAM, Pagination::DEFAULT_PER_PAGE));

        return $this->render('Portal::Teacher/Meetings', [
            'meetings' => MeetingResource::collection($meetings),
            'filters' => $request->only([Pagination::SEARCH_PARAM, 'status']),
            ...$this->lookups(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Meeting::class);

        return $this->render('Portal::Teacher/MeetingForm', $this->lookups());
    }

    public function store(TeacherStoreMeetingRequest $request, CreateMeetingAction $action): RedirectResponse
    {
        $this->authorize('create', Meeting::class);

        $action->execute(CreateMeetingDTO::fromRequest($request));

        return $this->toRoute('teacher.meetings', [], __('meeting::meeting.created'));
    }

    public function edit(Meeting $meeting): Response
    {
        $this->authorize('update', $meeting);

        return $this->render('Portal::Teacher/MeetingForm', [
            'meeting' => new MeetingResource($meeting),
            ...$this->lookups(),
        ]);
    }

    public function update(TeacherUpdateMeetingRequest $request, Meeting $meeting, UpdateMeetingAction $action): RedirectResponse
    {
        $this->authorize('update', $meeting);

        $action->execute(['meeting' => $meeting, 'dto' => UpdateMeetingDTO::fromRequest($request)]);

        return $this->toRoute('teacher.meetings', [], __('meeting::meeting.updated'));
    }

    public function start(Meeting $meeting, StartMeetingAction $action): RedirectResponse
    {
        $this->authorize('start', $meeting);

        $action->execute($meeting);

        return $this->backWithSuccess(__('meeting::meeting.started'));
    }

    public function finish(Meeting $meeting, FinishMeetingAction $action): RedirectResponse
    {
        $this->authorize('finish', $meeting);

        $action->execute($meeting);

        return $this->backWithSuccess(__('meeting::meeting.finished'));
    }

    /**
     * Lets an open room poll for the moment its meeting stops being live, so
     * ending it from the dashboard also closes it for everyone still inside.
     */
    public function status(Meeting $meeting, MeetingAccessGuard $guard): JsonResponse
    {
        $this->authorize('view', $meeting);

        abort_unless($guard->allows(request()->user(), $meeting), 403);

        return response()->json(['status' => $meeting->status]);
    }

    public function cancel(Meeting $meeting, CancelMeetingAction $action): RedirectResponse
    {
        $this->authorize('cancel', $meeting);

        $action->execute($meeting);

        return $this->backWithSuccess(__('meeting::meeting.cancelled'));
    }

    /**
     * @return array<string, mixed>
     */
    private function lookups(): array
    {
        return [
            'classes' => $this->scope->teacherClasses()
                ->map(fn (SchoolClass $class): array => ['id' => $class->id, 'name' => $class->name])
                ->values()
                ->all(),
            'subjects' => $this->scope->teacherSubjects()
                ->map(fn (ClassSubject $link): array => [
                    'id' => $link->subject_id,
                    'name' => $link->subject?->name,
                ])
                ->unique('id')
                ->values()
                ->all(),
        ];
    }
}
