<?php

namespace Modules\Assignment\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Academic\Services\AcademicContextService;
use Modules\Academic\Services\AcademicYearService;
use Modules\Assignment\Actions\ArchiveAssignmentAction;
use Modules\Assignment\Actions\CreateAssignmentAction;
use Modules\Assignment\Actions\RestoreAssignmentAction;
use Modules\Assignment\Actions\UpdateAssignmentAction;
use Modules\Assignment\DTOs\AssignmentFilterDTO;
use Modules\Assignment\DTOs\CreateAssignmentDTO;
use Modules\Assignment\DTOs\UpdateAssignmentDTO;
use Modules\Assignment\Http\Requests\StoreAssignmentRequest;
use Modules\Assignment\Http\Requests\UpdateAssignmentRequest;
use Modules\Assignment\Models\Assignment;
use Modules\Assignment\Queries\AssignmentQuery;
use Modules\Assignment\Transformers\AssignmentResource;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\SchoolClass\Models\ClassSubject;
use Modules\SchoolClass\Models\SchoolClass;
use Modules\Subject\Models\Subject;

class AssignmentController extends BaseInertiaController
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Assignment::class);

        $filters = AssignmentFilterDTO::fromRequest($request);

        $query = (new AssignmentQuery)
            ->searchTitle($filters->search)
            ->classId($filters->classId)
            ->subjectId($filters->subjectId)
            ->teacherId($filters->teacherId)
            ->academicYearId($filters->academicYearId)
            ->semesterId($filters->semesterId)
            ->isPublished($filters->isPublished)
            ->sort($filters->sortBy, $filters->sortOrder);

        $assignments = $query->paginate($filters->perPage);

        return $this->render('Assignment::Assignments/Index', [
            'assignments' => AssignmentResource::collection($assignments),
            'filters' => $request->only([
                Pagination::SEARCH_PARAM,
                'class_id',
                'subject_id',
                'teacher_id',
                'semester_id',
                'is_published',
            ]),
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
            'classes' => SchoolClass::query()->where('is_active', true)->where('academic_year_id', AcademicYearService::id())
                ->get(['id', 'name', 'academic_year_id']),

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
        ];
    }
}
