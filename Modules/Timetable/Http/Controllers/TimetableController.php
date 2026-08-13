<?php

namespace Modules\Timetable\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Academic\Models\AcademicYear;
use Modules\Academic\Models\GradeLevel;
use Modules\Academic\Models\Semester;
use Modules\Academic\Services\AcademicContextService;
use Modules\Classroom\Models\Classroom;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\Core\DTOs\ListQueryDTO;
use Modules\Core\Support\Filters\FilterOptions;
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

        $filters = ListQueryDTO::fromRequest($request, [
            'academic_year_id' => AcademicContextService::yearId(),
        ]);

        $query = new TimetableQuery;

        $timetables = $query
            ->search($filters->search)
            ->where($filters->values)
            ->sort($filters->sortBy, $filters->sortOrder)
            ->paginate($filters->perPage);

        return $this->render('Timetable::Timetables/Index', [
            'timetables' => TimetableResource::collection($timetables),
            'filters' => [
                Pagination::SEARCH_PARAM => $filters->search,
                ...$query->activeFilters($filters->values),
            ],
            'filterDefinitions' => $query->filterSchema([
                'class_id' => FilterOptions::fromModels(
                    SchoolClass::query()->where('is_active', true)->orderBy('name')->get(),
                ),
                'academic_year_id' => FilterOptions::fromModels(
                    AcademicYear::query()->orderByDesc('start_date')->get(),
                ),
                'semester_id' => FilterOptions::withNotSet(
                    FilterOptions::fromModels(Semester::query()->get()),
                ),
                'grade_level_id' => FilterOptions::fromModels(
                    GradeLevel::query()->where('is_active', true)->orderBy('display_order')->get(),
                ),
            ]),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Timetable::class);

        return $this->render('Timetable::Timetables/Create', $this->classLookups());
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
            'periods' => $this->activePeriods(),
            'weekdays' => $this->activeWeekdayOptions(),
        ]);
    }

    public function edit(Timetable $timetable): Response
    {
        $this->authorize('update', $timetable);

        $timetable->load(['class.gradeLevel', 'academicYear', 'semester', 'entries.subject', 'entries.teacher', 'entries.classroom', 'entries.period']);

        return $this->render('Timetable::Timetables/Edit', [
            'timetable' => new TimetableResource($timetable),
            'periods' => $this->activePeriods(),
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
     * The scheduling grid is built from these, so they must arrive as a plain
     * list. `PeriodResource::collection()` is an AnonymousResourceCollection,
     * which Inertia serialises through toResponse() and therefore keeps the
     * "data" envelope even though BaseResource unwraps single resources —
     * the page then read `periods.length` off an object and rendered an
     * empty grid.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function activePeriods(): array
    {
        $periods = Period::query()
            ->where('is_active', true)
            ->orderBy('period_number')
            ->get();

        return PeriodResource::collection($periods)->resolve(request());
    }

    /**
     * Classes for the create form's picker.
     *
     * Deliberately not scoped to an academic year: `classes.academic_year_id`
     * was dropped, because a class is year-independent. The timetable takes its
     * own year from the academic context when it is saved, so filtering the
     * picker by year would narrow it on a column that no longer exists — which
     * is what made this page fail with "Unknown column 'academic_year_id'".
     *
     * @return array<string, mixed>
     */
    protected function classLookups(): array
    {
        return [
            'classes' => SchoolClass::query()
                ->where('is_active', true)
                ->with(['gradeLevel:id,name'])
                ->get(['id', 'name', 'grade_level_id']),
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
