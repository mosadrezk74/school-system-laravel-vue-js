<?php

namespace Modules\Assignment\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Modules\Assignment\Actions\GradeAssignmentSubmissionAction;
use Modules\Assignment\Actions\SubmitAssignmentAction;
use Modules\Assignment\DTOs\GradeAssignmentSubmissionDTO;
use Modules\Assignment\DTOs\SubmitAssignmentDTO;
use Modules\Assignment\Http\Requests\GradeAssignmentSubmissionRequest;
use Modules\Assignment\Http\Requests\StoreAssignmentSubmissionRequest;
use Modules\Assignment\Models\Assignment;
use Modules\Assignment\Models\AssignmentSubmission;
use Modules\Core\Abstracts\BaseInertiaController;

class AssignmentSubmissionController extends BaseInertiaController
{
    public function store(StoreAssignmentSubmissionRequest $request, Assignment $assignment, SubmitAssignmentAction $action): RedirectResponse
    {
        $this->authorize('create', AssignmentSubmission::class);

        $action->execute(SubmitAssignmentDTO::fromRequest($request, $assignment->id));

        return $this->toRoute('assignments.show', ['assignment' => $assignment->id], __('assignment::assignment.submission_created'));
    }

    public function grade(GradeAssignmentSubmissionRequest $request, Assignment $assignment, AssignmentSubmission $submission, GradeAssignmentSubmissionAction $action): RedirectResponse
    {
        $this->authorize('update', $submission);

        $action->execute(['submission' => $submission, 'dto' => GradeAssignmentSubmissionDTO::fromRequest($request)]);

        return $this->toRoute('assignments.show', ['assignment' => $assignment->id], __('assignment::assignment.submission_graded'));
    }
}
