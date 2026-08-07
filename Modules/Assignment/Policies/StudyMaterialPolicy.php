<?php

namespace Modules\Assignment\Policies;

use App\Models\User;
use Modules\Assignment\Models\StudyMaterial;
use Modules\Portal\Traits\ChecksPortalOwnership;

class StudyMaterialPolicy
{
    use ChecksPortalOwnership;

    public function viewAny(User $user): bool
    {
        return $user->can('studyMaterial.view') && $this->ownsClassOrSchoolWide($studyMaterial->class_id);
    }

    public function view(User $user, StudyMaterial $studyMaterial): bool
    {
        return $user->can('studyMaterial.view');
    }

    public function create(User $user): bool
    {
        return $user->can('studyMaterial.create');
    }

    public function update(User $user, StudyMaterial $studyMaterial): bool
    {
        return $user->can('studyMaterial.update');
    }

    public function delete(User $user, StudyMaterial $studyMaterial): bool
    {
        return $user->can('studyMaterial.delete');
    }

    public function restore(User $user, StudyMaterial $studyMaterial): bool
    {
        return $user->can('studyMaterial.delete');
    }
}
