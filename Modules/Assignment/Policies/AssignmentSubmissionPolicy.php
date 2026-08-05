<?php

namespace Modules\Assignment\Policies;

use App\Models\User;
use Modules\Assignment\Models\AssignmentSubmission;

class AssignmentSubmissionPolicy
{
    public function view(User $user, AssignmentSubmission $submission): bool
    {
        return $user->can('assignmentSubmission.view');
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
