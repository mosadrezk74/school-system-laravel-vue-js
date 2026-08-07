<?php

namespace Modules\StudyMaterial\Policies;

use App\Models\User;
use Modules\Auth\Enums\UserRole;
use Modules\StudyMaterial\Models\StudyResource;

class StudyResourcePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('study-material.view');
    }

    public function view(User $user, StudyResource $studyResource): bool
    {
        return $user->can('study-material.view');
    }

    public function download(User $user, StudyResource $studyResource): bool
    {
        return $user->can('study-material.view');
    }

    public function create(User $user): bool
    {
        return $user->can('study-material.create');
    }

    public function update(User $user, StudyResource $studyResource): bool
    {
        return $user->can('study-material.update') && $this->ownsOrManages($user, $studyResource);
    }

    public function delete(User $user, StudyResource $studyResource): bool
    {
        return $user->can('study-material.delete') && $this->ownsOrManages($user, $studyResource);
    }

    public function restore(User $user, StudyResource $studyResource): bool
    {
        return $user->can('study-material.delete') && $this->ownsOrManages($user, $studyResource);
    }

    private function ownsOrManages(User $user, StudyResource $studyResource): bool
    {
        if ($user->role !== UserRole::Teacher) {
            return true;
        }

        return $studyResource->teacher_id === $user->id;
    }
}
