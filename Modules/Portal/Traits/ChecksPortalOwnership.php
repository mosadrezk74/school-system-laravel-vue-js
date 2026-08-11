<?php

namespace Modules\Portal\Traits;

use Modules\Portal\Services\PortalScopeService;

trait ChecksPortalOwnership
{
    protected function scope(): PortalScopeService
    {
        return app(PortalScopeService::class);
    }

    protected function ownsStudent(?int $studentId): bool
    {
        return $this->scope()->canAccessStudent($studentId);
    }

    protected function ownsClass(?int $classId): bool
    {
        return $this->scope()->canAccessClass($classId);
    }

    /**
     * A null class means the record is school-wide rather than tied to one
     * class, so it stays visible to everyone who holds the permission.
     */
    protected function ownsClassOrSchoolWide(?int $classId): bool
    {
        return $classId === null || $this->ownsClass($classId);
    }

    protected function ownsSubject(?int $subjectId): bool
    {
        return $this->scope()->canAccessSubject($subjectId);
    }

    protected function ownsClassSubject(?int $classId, ?int $subjectId): bool
    {
        return $this->scope()->canAccessClassSubject($classId, $subjectId);
    }

    protected function ownsTeacherRecord(?int $teacherId): bool
    {
        $scope = $this->scope();

        if ($scope->isStaff()) {
            return true;
        }

        return $teacherId !== null && $teacherId === $scope->teacherUserId();
    }
}
