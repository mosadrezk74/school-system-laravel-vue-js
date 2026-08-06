<?php

namespace Modules\Examination\Policies;

use App\Models\User;
use Modules\Examination\Models\ExamGrade;

class ExamGradePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('examGrade.view');
    }

    public function view(User $user, ExamGrade $examGrade): bool
    {
        return $user->can('examGrade.view');
    }

    public function create(User $user): bool
    {
        return $user->can('examGrade.create');
    }

    public function update(User $user, ExamGrade $examGrade): bool
    {
        return $user->can('examGrade.update');
    }

    public function delete(User $user, ExamGrade $examGrade): bool
    {
        return $user->can('examGrade.delete');
    }
}
