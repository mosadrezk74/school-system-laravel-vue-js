<?php

namespace Modules\Portal\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Assignment\Actions\ArchiveAssignmentAction;
use Modules\Assignment\Actions\CreateAssignmentAction;
use Modules\Assignment\Actions\UpdateAssignmentAction;
use Modules\Assignment\DTOs\CreateAssignmentDTO;
use Modules\Assignment\DTOs\UpdateAssignmentDTO;
use Modules\Assignment\Models\Assignment;
use Modules\Assignment\Queries\AssignmentQuery;
use Modules\Assignment\Transformers\AssignmentResource;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\Portal\Http\Requests\TeacherStoreAssignmentRequest;
use Modules\Portal\Http\Requests\TeacherUpdateAssignmentRequest;
use Modules\Portal\Services\PortalScopeService;
use Modules\SchoolClass\Models\ClassSubject;
use Modules\SchoolClass\Models\SchoolClass;

class TeacherHomeworkController extends BaseInertiaController
{
    public function __construct(private readonly PortalScopeService $scope) {}

    public function index(Request $request): Response
    {
        $classId = $request->filled('class_id') ? (int) $request->input('class_id') : null;

        $assignments = (new AssignmentQuery)
            ->teacherId($this->scope->teacherUserId())
            ->classId($this->scope->canAccessClass($classId) ? $classId : null)
            ->searchTitle($request->string(Pagination::SEARCH_PARAM)->toString() ?: null)
            ->sort('deadline', 'desc')
            ->paginate($request->integer(Pagination::PER_PAGE_PARAM, Pagination::DEFAULT_PER_PAGE));

        return $this->render('Portal::Teacher/Homework', [
            'assignments' => AssignmentResource::collection($assignments),
            'filters' => $request->only([Pagination::SEARCH_PARAM, 'class_id']),
            ...$this->lookups(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Assignment::class);

        return $this->render('Portal::Teacher/HomeworkForm', $this->lookups());
    }

    public function store(TeacherStoreAssignmentRequest $request, CreateAssignmentAction $action): RedirectResponse
    {
        $this->authorize('create', Assignment::class);

        abort_unless($this->scope->canAccessClass((int) $request->validated('class_id')), 403);

        $action->execute(CreateAssignmentDTO::fromRequest($request));

        return $this->toRoute('teacher.homework', [], __('assignment::assignment.created'));
    }

    public function edit(Assignment $assignment): Response
    {
        $this->authorize('update', $assignment);

        abort_unless($this->owns($assignment), 403);

        return $this->render('Portal::Teacher/HomeworkForm', [
            'assignment' => new AssignmentResource($assignment),
            ...$this->lookups(),
        ]);
    }

    public function update(TeacherUpdateAssignmentRequest $request, Assignment $assignment, UpdateAssignmentAction $action): RedirectResponse
    {
        $this->authorize('update', $assignment);

        abort_unless($this->owns($assignment), 403);
        abort_unless($this->scope->canAccessClass((int) $request->validated('class_id')), 403);

        $action->execute(['assignment' => $assignment, 'dto' => UpdateAssignmentDTO::fromRequest($request)]);

        return $this->toRoute('teacher.homework', [], __('assignment::assignment.updated'));
    }

    public function destroy(Assignment $assignment, ArchiveAssignmentAction $action): RedirectResponse
    {
        $this->authorize('delete', $assignment);

        abort_unless($this->owns($assignment), 403);

        $action->execute($assignment);

        return $this->toRoute('teacher.homework', [], __('assignment::assignment.archived'));
    }

    private function owns(Assignment $assignment): bool
    {
        return $assignment->teacher_id === $this->scope->teacherUserId();
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
                    'class_id' => $link->class_id,
                ])
                ->unique('id')
                ->values()
                ->all(),
        ];
    }
}
