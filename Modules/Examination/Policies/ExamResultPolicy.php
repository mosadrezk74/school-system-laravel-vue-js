<?php

namespace Modules\Examination\Policies;

use App\Models\User;
use Modules\Examination\Models\ExamResult;
use Modules\Portal\Traits\ChecksPortalOwnership;

class ExamResultPolicy
{
    use ChecksPortalOwnership;

    public function viewAny(User $user): bool
    {
        return $user->can('exam-result.view');
    }

    public function view(User $user, ExamResult $examResult): bool
    {
        return $user->can('exam-result.view') && $this->ownsStudent($examResult->student_id);
    }

    public function create(User $user): bool
    {
        return $user->can('exam-result.create');
    }

    public function update(User $user, ExamResult $examResult): bool
    {
        return $user->can('exam-result.update') && $this->ownsStudent($examResult->student_id);
    }

    public function delete(User $user, ExamResult $examResult): bool
    {
        return $user->can('exam-result.delete') && $this->ownsStudent($examResult->student_id);
    }
}
