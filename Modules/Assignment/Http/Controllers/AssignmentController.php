<?php

namespace Modules\Assignment\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Academic\Models\AcademicYear;
use Modules\Academic\Models\Semester;
use Modules\Academic\Services\AcademicContextService;
use Modules\Assignment\Actions\ArchiveAssignmentAction;
use Modules\Assignment\Actions\CreateAssignmentAction;
use Modules\Assignment\Actions\RestoreAssignmentAction;
use Modules\Assignment\Actions\UpdateAssignmentAction;
use Modules\Assignment\DTOs\CreateAssignmentDTO;
use Modules\Assignment\DTOs\UpdateAssignmentDTO;
use Modules\Assignment\Enums\AssignmentType;
use Modules\Assignment\Http\Requests\StoreAssignmentRequest;
use Modules\Assignment\Http\Requests\UpdateAssignmentRequest;
use Modules\Assignment\Models\Assignment;
use Modules\Assignment\Queries\AssignmentQuery;
use Modules\Assignment\Transformers\AssignmentResource;
use Modules\Auth\Enums\UserRole;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\Core\DTOs\ListQueryDTO;
use Modules\Core\Support\Filters\FilterOptions;
use Modules\SchoolClass\Models\ClassSubject;
use Modules\SchoolClass\Models\SchoolClass;
use Modules\Subject\Models\Subject;

class AssignmentController extends BaseInertiaController
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Assignment::class);

        // The current year is a default, not a lock: `academic_year_id` stays
        // an explicit historical filter the user can move.
        $filters = ListQueryDTO::fromRequest($request, [
            'academic_year_id' => AcademicContextService::yearId(),
        ]);

        $query = new AssignmentQuery;

        $assignments = $query
            ->search($filters->search)
            ->where($filters->values)
            ->sort($filters->sortBy, $filters->sortOrder)
            ->paginate($filters->perPage);

        return $this->render('Assignment::Assignments/Index', [
            'assignments' => AssignmentResource::collection($assignments),
            'filters' => [
                Pagination::SEARCH_PARAM => $filters->search,
                ...$query->activeFilters($filters->values),
            ],
            'filterDefinitions' => $query->filterSchema($this->filterOptions()),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Assignment::class);

        return $this->render('Assignment::Assignments/Create', $this->formOptions());
    }

    public function store(StoreAssignmentRequest $request, CreateAssignmentAction $action): RedirectResponse
    {
        $this->authorize('create', Assignment::class);

        $action->execute(CreateAssignmentDTO::fromRequest($request));

        return $this->toRoute('assignments.index', [], __('assignment::assignment.created'));
    }

    public function show(Assignment $assignment): Response
    {
        $this->authorize('view', $assignment);

        $assignment->load(['class', 'subject', 'teacher', 'academicYear', 'semester', 'submissions.student.user']);

        return $this->render('Assignment::Assignments/Show', [
            'assignment' => new AssignmentResource($assignment),
        ]);
    }

    public function edit(Assignment $assignment): Response
    {
        $this->authorize('update', $assignment);

        return $this->render('Assignment::Assignments/Edit', [
            'assignment' => new AssignmentResource($assignment),
            ...$this->formOptions(),
        ]);
    }

    public function update(UpdateAssignmentRequest $request, Assignment $assignment, UpdateAssignmentAction $action): RedirectResponse
    {
        $this->authorize('update', $assignment);

        $action->execute(['assignment' => $assignment, 'dto' => UpdateAssignmentDTO::fromRequest($request)]);

        return $this->toRoute('assignments.index', [], __('assignment::assignment.updated'));
    }

    public function destroy(Assignment $assignment, ArchiveAssignmentAction $action): RedirectResponse
    {
        $this->authorize('delete', $assignment);

        $action->execute($assignment);

        return $this->toRoute('assignments.index', [], __('assignment::assignment.archived'));
    }

    public function restore(int $assignment, RestoreAssignmentAction $action): RedirectResponse
    {
        $assignment = Assignment::withTrashed()->findOrFail($assignment);

        $this->authorize('restore', $assignment);

        $action->execute($assignment);

        return $this->toRoute('assignments.index', [], __('assignment::assignment.restored'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function formOptions(): array
    {
        $context = app(AcademicContextService::class);
        $semester = $context->currentSemester();

        return [
            'classes' => SchoolClass::query()->where('is_active', true)
                ->get(['id', 'name']),

            /*
             * One eager-loaded pass over the real teaching assignments feeds
             * both cascading dropdowns, so the form never offers a subject the
             * class does not study or a teacher who does not teach it.
             */
            'classSubjects' => ClassSubject::query()
                ->with(['subject:id,name', 'teacher:id,name'])
                ->get()
                ->map(fn (ClassSubject $link): array => [
                    'class_id' => $link->class_id,
                    'subject_id' => $link->subject_id,
                    'subject_name' => $link->subject?->name,
                    'teacher_id' => $link->teacher_id,
                    'teacher_name' => $link->teacher?->name,
                ])
                ->values(),

            'currentSemester' => $semester ? ['id' => $semester->id, 'name' => $semester->name] : null,

            /*
             * From the same enum the rule validates against. This was a free
             * text box seeded with an empty string, so it held whatever each
             * teacher happened to type.
             */
            'assignmentTypes' => AssignmentType::selectOptions(),
        ];
    }

    /**
     * Option lists for the filter bar's selects.
     *
     * @return array<string, array<int, array{title: string, value: mixed}>>
     */
    private function filterOptions(): array
    {
        return [
            'class_id' => FilterOptions::fromModels(
                SchoolClass::query()->where('is_active', true)->orderBy('name')->get(),
            ),
            'subject_id' => FilterOptions::fromModels(
                Subject::query()->where('is_active', true)->get(),
            ),
            'teacher_id' => FilterOptions::from(
                User::query()->where('role', UserRole::Teacher->value)->get(),
                fn (User $teacher): string => (string) $teacher->name,
            ),
            'academic_year_id' => FilterOptions::fromModels(
                AcademicYear::query()->orderByDesc('start_date')->get(),
            ),
            'semester_id' => FilterOptions::withNotSet(
                FilterOptions::fromModels(Semester::query()->get()),
            ),
        ];
    }
}
