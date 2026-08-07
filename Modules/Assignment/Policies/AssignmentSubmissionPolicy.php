<?php

namespace Modules\Assignment\Policies;

use App\Models\User;
use Modules\Assignment\Models\AssignmentSubmission;
use Modules\Portal\Traits\ChecksPortalOwnership;

class AssignmentSubmissionPolicy
{
    use ChecksPortalOwnership;

    public function view(User $user, AssignmentSubmission $submission): bool
    {
        return $user->can('assignmentSubmission.view') && $this->ownsStudent($submission->student_id);
    }

    public function create(User $user): bool
    {
        return $user->can('assignmentSubmission.create');
    }

    public function update(User $user, AssignmentSubmission $submission): bool
    {
        return $user->can('assignmentSubmission.update');
    }
}
