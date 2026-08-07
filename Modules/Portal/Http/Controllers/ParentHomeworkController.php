<?php

namespace Modules\Portal\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Assignment\Models\AssignmentSubmission;
use Modules\Assignment\Queries\AssignmentQuery;
use Modules\Assignment\Transformers\AssignmentResource;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\Portal\Services\PortalScopeService;
use Modules\Portal\Traits\ResolvesGuardianChildren;

class ParentHomeworkController extends BaseInertiaController
{
    use ResolvesGuardianChildren;

    public function __construct(private readonly PortalScopeService $scope) {}

    public function index(Request $request): Response
    {
        $child = $this->selectedChild($request);

        $assignments = (new AssignmentQuery)
            ->classId($child?->current_class_id ?? -1)
            ->isPublished(true)
            ->sort('deadline', 'desc')
            ->paginate($request->integer(Pagination::PER_PAGE_PARAM, Pagination::DEFAULT_PER_PAGE));

        return $this->render('Portal::Parent/Homework', [
            'assignments' => AssignmentResource::collection($assignments),
            'submissions' => $child ? $this->submissionsFor($child->id) : [],
            'children' => $this->childOptions(),
            'selectedChild' => $child?->id,
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function submissionsFor(int $studentId): array
    {
        return AssignmentSubmission::query()
            ->where('student_id', $studentId)
            ->get(['assignment_id', 'submitted_at', 'score', 'is_graded', 'is_late'])
            ->keyBy('assignment_id')
            ->map(fn (AssignmentSubmission $submission): array => [
                'submitted_at' => $submission->submitted_at?->toIso8601String(),
                'score' => $submission->score,
                'is_graded' => (bool) $submission->is_graded,
                'is_late' => (bool) $submission->is_late,
            ])
            ->all();
    }
}
