<?php

namespace Modules\Portal\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Assignment\Actions\SubmitAssignmentAction;
use Modules\Assignment\DTOs\SubmitAssignmentDTO;
use Modules\Assignment\Models\Assignment;
use Modules\Assignment\Models\AssignmentSubmission;
use Modules\Assignment\Queries\AssignmentQuery;
use Modules\Assignment\Transformers\AssignmentResource;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\Portal\Http\Requests\StudentStoreSubmissionRequest;
use Modules\Portal\Services\PortalScopeService;

class StudentHomeworkController extends BaseInertiaController
{
    public function __construct(private readonly PortalScopeService $scope) {}

    public function index(Request $request): Response
    {
        $classIds = $this->scope->accessibleClassIds() ?? [];

        $assignments = (new AssignmentQuery)
            ->search($request->string(Pagination::SEARCH_PARAM)->toString() ?: null)
            // Scope values, supplied here rather than read from the request,
            // so a portal user cannot widen them from the URL.
            ->where([
                'class_id' => $classIds === [] ? -1 : $classIds[0],
                'is_published' => true,
            ])
            ->sort('deadline', 'desc')
            ->paginate($request->integer(Pagination::PER_PAGE_PARAM, Pagination::DEFAULT_PER_PAGE));

        return $this->render('Portal::Student/Homework', [
            'assignments' => AssignmentResource::collection($assignments),
            'filters' => $request->only([Pagination::SEARCH_PARAM]),
            'submissions' => $this->mySubmissions(),
        ]);
    }

    public function show(Assignment $assignment): Response
    {
        $this->authorize('view', $assignment);

        $assignment->load(['subject', 'schoolClass', 'teacher']);

        return $this->render('Portal::Student/HomeworkDetail', [
            'assignment' => new AssignmentResource($assignment),
            'submission' => $this->submissionFor($assignment->id),
        ]);
    }

    public function submit(StudentStoreSubmissionRequest $request, Assignment $assignment, SubmitAssignmentAction $action): RedirectResponse
    {
        $this->authorize('view', $assignment);

        $action->execute(SubmitAssignmentDTO::fromRequest($request, $assignment->id));

        return $this->toRoute('student.homework', [], __('assignment::assignment.submitted'));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function mySubmissions(): array
    {
        return AssignmentSubmission::query()
            ->where('student_id', $this->scope->currentStudentProfile()?->id)
            ->get(['assignment_id', 'submitted_at', 'score', 'grade', 'is_graded', 'is_late'])
            ->keyBy('assignment_id')
            ->map(fn (AssignmentSubmission $submission): array => [
                'submitted_at' => $submission->submitted_at?->toIso8601String(),
                'score' => $submission->score,
                'grade' => $submission->grade,
                'is_graded' => (bool) $submission->is_graded,
                'is_late' => (bool) $submission->is_late,
            ])
            ->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function submissionFor(int $assignmentId): ?array
    {
        return $this->mySubmissions()[$assignmentId] ?? null;
    }
}
