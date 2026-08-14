<?php

namespace Modules\Meeting\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Academic\Models\Semester;
use Modules\Academic\Services\AcademicContextService;
use Modules\Auth\Enums\UserRole;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\Core\DTOs\ListQueryDTO;
use Modules\Core\Support\Filters\FilterOptions;
use Modules\Meeting\Actions\CancelMeetingAction;
use Modules\Meeting\Actions\CreateMeetingAction;
use Modules\Meeting\Actions\DeleteMeetingAction;
use Modules\Meeting\Actions\FinishMeetingAction;
use Modules\Meeting\Actions\StartMeetingAction;
use Modules\Meeting\Actions\StartMeetingForSessionAction;
use Modules\Meeting\Actions\UpdateMeetingAction;
use Modules\Meeting\DTOs\CreateMeetingDTO;
use Modules\Meeting\DTOs\UpdateMeetingDTO;
use Modules\Meeting\Http\Requests\StoreMeetingRequest;
use Modules\Meeting\Http\Requests\UpdateMeetingRequest;
use Modules\Meeting\Models\Meeting;
use Modules\Meeting\Queries\MeetingQuery;
use Modules\Meeting\Services\JitsiRoomFactory;
use Modules\Meeting\Services\MeetingAccessGuard;
use Modules\Meeting\Services\MeetingMediaDefaults;
use Modules\Meeting\Transformers\MeetingResource;
use Modules\SchoolClass\Models\SchoolClass;
use Modules\Subject\Models\Subject;
use Modules\Timetable\Models\TimetableEntry;

class MeetingController extends BaseInertiaController
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Meeting::class);

        $filters = ListQueryDTO::fromRequest($request);

        $user = $request->user();
        $isTeacher = $user->role === UserRole::Teacher;
        $isStudent = $user->role === UserRole::Student;

        $query = new MeetingQuery;

        /*
         * Role scoping is applied to the query itself, and the keys it decides
         * are dropped from the filter input — otherwise a teacher could pass
         * ?teacher_id= to read another teacher's meetings, or a student
         * ?status= to see ones that are not live.
         */
        $values = $filters->values;

        if ($isTeacher) {
            $query->teacherId($user->id);
            unset($values['teacher_id']);
        }

        if ($isStudent) {
            $query->status('live');
            unset($values['status']);
        }

        $meetings = $query
            ->search($filters->search)
            ->where($values)
            ->sort($filters->sortBy, $filters->sortOrder)
            ->paginate($filters->perPage);

        return $this->render('Meeting::Meetings/Index', [
            'meetings' => MeetingResource::collection($meetings),
            'filters' => [
                Pagination::SEARCH_PARAM => $filters->search,
                ...$query->activeFilters($values),
            ],
            'filterDefinitions' => $query->filterSchema([
                'teacher_id' => FilterOptions::from(
                    User::query()->where('role', UserRole::Teacher->value)->get(),
                    fn (User $teacher): string => (string) $teacher->name,
                ),
                'subject_id' => FilterOptions::withNotSet(
                    FilterOptions::fromModels(Subject::query()->where('is_active', true)->get()),
                ),
                'school_class_id' => FilterOptions::withNotSet(
                    FilterOptions::fromModels(
                        SchoolClass::query()->where('is_active', true)->orderBy('name')->get(),
                    ),
                ),
            ]),
            'teachers' => User::query()->where('role', UserRole::Teacher->value)->get(['id', 'name']),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Meeting::class);

        return $this->render('Meeting::Meetings/Create', $this->formOptions());
    }

    public function store(StoreMeetingRequest $request, CreateMeetingAction $action): RedirectResponse
    {
        $this->authorize('create', Meeting::class);

        $action->execute(CreateMeetingDTO::fromRequest($request));

        return $this->toRoute('meetings.index', [], __('meeting::meeting.created'));
    }

    public function show(Meeting $meeting): Response
    {
        $this->authorize('view', $meeting);

        $meeting->load(['teacher', 'academicYear', 'semester', 'subject', 'schoolClass']);

        return $this->render('Meeting::Meetings/Show', [
            'meeting' => new MeetingResource($meeting),
            'can' => [
                'manage' => auth()->user()->can('update', $meeting),
            ],
        ]);
    }

    /**
     * Renders the in-app meeting room. The Jitsi conference is embedded here
     * rather than opened on meet.etqan.cloud, so the user never leaves the
     * application.
     */
    public function join(Request $request, Meeting $meeting, MeetingAccessGuard $guard): Response
    {
        $this->authorize('join', $meeting);

        abort_unless($guard->allows($request->user(), $meeting), 403);

        $meeting->load(['teacher', 'subject', 'schoolClass']);

        $user = $request->user();

        return $this->render('Meeting::Meetings/Join', [
            'meeting' => new MeetingResource($meeting),

            /*
             * The room name is read from the stored record, never from user
             * input, so nobody can point the embed at an arbitrary room.
             */
            'jitsi' => [
                'domain' => app(JitsiRoomFactory::class)->domain(),
                'room_name' => $meeting->room_name,
                'display_name' => $user->name,
                'email' => $user->email,
                ...app(MeetingMediaDefaults::class)->toArray(),
            ],
            'can' => [
                'manage' => $user->can('update', $meeting),
            ],
            'statusUrl' => route('meetings.status', $meeting),
            'returnUrl' => route('meetings.index'),
        ]);
    }

    /**
     * Reports the meeting's lifecycle state to an open room. Read-only, and
     * gated by the same view checks as the room itself.
     */
    public function status(Request $request, Meeting $meeting, MeetingAccessGuard $guard): JsonResponse
    {
        $this->authorize('view', $meeting);

        abort_unless($guard->allows($request->user(), $meeting), 403);

        return response()->json(['status' => $meeting->status]);
    }

    public function edit(Meeting $meeting): Response
    {
        $this->authorize('update', $meeting);

        return $this->render('Meeting::Meetings/Edit', [
            'meeting' => new MeetingResource($meeting),
            ...$this->formOptions(),
        ]);
    }

    public function update(UpdateMeetingRequest $request, Meeting $meeting, UpdateMeetingAction $action): RedirectResponse
    {
        $this->authorize('update', $meeting);

        $action->execute(['meeting' => $meeting, 'dto' => UpdateMeetingDTO::fromRequest($request)]);

        return $this->toRoute('meetings.index', [], __('meeting::meeting.updated'));
    }

    public function destroy(Meeting $meeting, DeleteMeetingAction $action): RedirectResponse
    {
        $this->authorize('delete', $meeting);

        $action->execute($meeting);

        return $this->toRoute('meetings.index', [], __('meeting::meeting.deleted'));
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

    public function cancel(Meeting $meeting, CancelMeetingAction $action): RedirectResponse
    {
        $this->authorize('cancel', $meeting);

        $action->execute($meeting);

        return $this->backWithSuccess(__('meeting::meeting.cancelled'));
    }

    public function startForEntry(TimetableEntry $entry, StartMeetingForSessionAction $action): RedirectResponse
    {
        $this->authorize('meeting.startForEntry', $entry);

        $meeting = $action->execute(['entry' => $entry, 'date' => now()]);

        return $this->toRoute('meetings.show', ['meeting' => $meeting->id]);
    }

    /**
     * Lookups for the create/edit form. Class and subject are what the student
     * and guardian meeting lists filter on, so the form has to be able to set
     * them — a meeting saved without a class reaches nobody outside staff.
     *
     * @return array<string, mixed>
     */
    protected function formOptions(): array
    {
        return [
            'teachers' => User::query()->where('role', UserRole::Teacher->value)->get(['id', 'name']),
            'semesters' => Semester::query()->where('academic_year_id', AcademicContextService::yearId())->get()
                ->map(fn (Semester $semester) => [
                    'id' => $semester->id,
                    'name' => $semester->name,
                    'academic_year_id' => $semester->academic_year_id,
                ])
                ->values(),
            'classes' => SchoolClass::query()->where('is_active', true)->orderBy('name')->get()
                ->map(fn (SchoolClass $class) => ['id' => $class->id, 'name' => $class->name])
                ->values(),
            'subjects' => Subject::query()->where('is_active', true)->get()
                ->map(fn (Subject $subject) => ['id' => $subject->id, 'name' => $subject->name])
                ->values(),
        ];
    }
}
