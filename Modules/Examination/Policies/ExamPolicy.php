<?php

namespace Modules\Examination\Policies;

use App\Models\User;
use Modules\Examination\Models\Exam;
use Modules\Portal\Traits\ChecksPortalOwnership;

class ExamPolicy
{
    use ChecksPortalOwnership;

    public function viewAny(User $user): bool
    {
        return $user->can('exam.view') && $this->ownsClassOrSchoolWide($exam->class_id);
    }

    public function view(User $user, Exam $exam): bool
    {
        return $user->can('exam.view');
    }

    public function create(User $user): bool
    {
        return $user->can('exam.create');
    }

    public function update(User $user, Exam $exam): bool
    {
        return $user->can('exam.update');
    }

    public function delete(User $user, Exam $exam): bool
    {
        return $user->can('exam.delete');
    }

    public function restore(User $user, Exam $exam): bool
    {
        return $user->can('exam.delete');
    }
}
