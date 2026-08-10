<?php

namespace Modules\Timetable\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Academic\Services\AcademicYearService;
use Modules\Classroom\Models\Classroom;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\SchoolClass\Models\SchoolClass;
use Modules\Setting\Services\SettingsService;
use Modules\Subject\Models\Subject;
use Modules\Teacher\Models\TeacherProfile;
use Modules\Timetable\Actions\ArchiveTimetableAction;
use Modules\Timetable\Actions\CreateTimetableAction;
use Modules\Timetable\Actions\RestoreTimetableAction;
use Modules\Timetable\Actions\UpdateTimetableAction;
use Modules\Timetable\DTOs\CreateTimetableDTO;
use Modules\Timetable\Enums\Weekday;
use Modules\Timetable\Http\Requests\StoreTimetableRequest;
use Modules\Timetable\Http\Requests\UpdateTimetableRequest;
use Modules\Timetable\Models\Period;
use Modules\Timetable\Models\Timetable;
use Modules\Timetable\Queries\TimetableQuery;
use Modules\Timetable\Transformers\PeriodResource;
use Modules\Timetable\Transformers\TimetableResource;

class TimetableController extends BaseInertiaController
{
    public function __construct(private readonly SettingsService $settings) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Timetable::class);

        $query = (new TimetableQuery)
            ->classId($request->integer('class_id') ?: null)
            ->academicYearId(AcademicYearService::id())
            ->isActive($request->has('is_active') ? $request->boolean('is_active') : null)
            ->sort('created_at', 'desc');

        $timetables = $query->paginate((int) $request->input(Pagination::PER_PAGE_PARAM, Pagination::DEFAULT_PER_PAGE));

        return $this->render('Timetable::Timetables/Index', [
            'timetables' => TimetableResource::collection($timetables),
            'filters' => $request->only(['class_id', 'is_active']),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Timetable::class);

        return $this->render('Timetable::Timetables/Create', $this->classAndYearLookups());
    }

    public function store(StoreTimetableRequest $request, CreateTimetableAction $action): RedirectResponse
    {
        $this->authorize('create', Timetable::class);

        $timetable = $action->execute(CreateTimetableDTO::fromRequest($request));

        return $this->toRoute('timetables.edit', ['timetable' => $timetable], __('timetable::timetable.created'));
    }

    public function show(Timetable $timetable): Response
    {
        $this->authorize('view', $timetable);

        $timetable->load(['class.gradeLevel', 'academicYear', 'semester', 'entries.subject', 'entries.teacher', 'entries.classroom', 'entries.period']);

        return $this->render('Timetable::Timetables/Show', [
            'timetable' => new TimetableResource($timetable),
            'periods' => PeriodResource::collection(Period::query()->where('is_active', true)->orderBy('period_number')->get()),
            'weekdays' => $this->activeWeekdayOptions(),
        ]);
    }

    public function edit(Timetable $timetable): Response
    {
        $this->authorize('update', $timetable);

        $timetable->load(['class.gradeLevel', 'academicYear', 'semester', 'entries.subject', 'entries.teacher', 'entries.classroom', 'entries.period']);

        return $this->render('Timetable::Timetables/Edit', [
            'timetable' => new TimetableResource($timetable),
            'periods' => PeriodResource::collection(Period::query()->where('is_active', true)->orderBy('period_number')->get()),
            'weekdays' => $this->activeWeekdayOptions(),
            ...$this->referenceLookups(),
        ]);
    }

    public function update(UpdateTimetableRequest $request, Timetable $timetable, UpdateTimetableAction $action): RedirectResponse
    {
        $this->authorize('update', $timetable);

        $action->execute(['timetable' => $timetable, 'dto' => CreateTimetableDTO::fromRequest($request)]);

        return $this->toRoute('timetables.edit', ['timetable' => $timetable], __('timetable::timetable.updated'));
    }

    public function destroy(Timetable $timetable, ArchiveTimetableAction $action): RedirectResponse
    {
        $this->authorize('delete', $timetable);

        $action->execute($timetable);

        return $this->toRoute('timetables.index', [], __('timetable::timetable.archived'));
    }

    public function restore(int $timetable, RestoreTimetableAction $action): RedirectResponse
    {
        $timetable = Timetable::withTrashed()->findOrFail($timetable);

        $this->authorize('restore', $timetable);

        $action->execute($timetable);

        return $this->toRoute('timetables.index', [], __('timetable::timetable.restored'));
    }

    /**
     * @return array<int, array{value: int, label: string}>
     */
    protected function activeWeekdayOptions(): array
    {
        $activeWeekdays = $this->settings->value('active_weekdays', Weekday::values());

        return collect(Weekday::options())
            ->filter(fn (array $option) => in_array($option['value'], $activeWeekdays, true))
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function classAndYearLookups(): array
    {
        return [
            'classes' => SchoolClass::query()->where('is_active', true)->where('academic_year_id', AcademicYearService::id())
                ->with(['gradeLevel:id,name'])
                ->get(['id', 'name', 'grade_level_id', 'academic_year_id']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function referenceLookups(): array
    {
        return [
            'subjects' => Subject::query()->get()->map(fn (Subject $subject) => ['id' => $subject->id, 'name' => $subject->name])->values(),
            'classrooms' => Classroom::query()->where('is_active', true)->get()
                ->map(fn (Classroom $classroom) => ['id' => $classroom->id, 'name' => $classroom->name])
                ->values(),
            'teachers' => TeacherProfile::query()
                ->where('is_active', true)
                ->with(['user:id,name', 'subjects:id'])
                ->get()
                ->map(fn (TeacherProfile $teacher) => [
                    'id' => $teacher->user_id,
                    'name' => $teacher->user->name,
                    'subject_ids' => $teacher->subjects->pluck('id')->values(),
                ])
                ->values(),
        ];
    }
}
