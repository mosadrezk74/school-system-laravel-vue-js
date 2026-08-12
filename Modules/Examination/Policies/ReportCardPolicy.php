<?php

namespace Modules\Examination\Policies;

use App\Models\User;
use Modules\Portal\Traits\ChecksPortalOwnership;
use Modules\Student\Models\StudentProfile;

/**
 * Who may read whose report card.
 *
 * Ownership is delegated to PortalScopeService through ChecksPortalOwnership,
 * the same authority the grade pages and ExamResultPolicy already use — a
 * student resolves to their own profile id, a guardian to their children, a
 * teacher to the students in classes they teach, staff to everyone.
 *
 * Registered as the named abilities `viewAnyReportCard` and `viewReportCard`
 * rather than as StudentProfile's policy, which StudentPolicy already owns.
 */
class ReportCardPolicy
{
    use ChecksPortalOwnership;

    /**
     * Reading report cards at all.
     *
     * Students and guardians are granted exam-result.view so their own portal
     * works, so the permission alone cannot gate the staff browser — without
     * the scope test below a family could open the admin screen and page
     * through the school. Staff and teachers browse; a family reaches only the
     * portal route, which resolves the subject from the signed-in user.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('exam-result.view') && $this->canBrowseReportCards();
    }

    /**
     * The portal route serves the signed-in person and their children, so it
     * asks only for the permission and then authorises the specific student.
     */
    public function viewOwn(User $user): bool
    {
        return $user->can('exam-result.view');
    }

    private function canBrowseReportCards(): bool
    {
        $scope = $this->scope();

        return $scope->isStaff() || $scope->isTeacher();
    }

    /**
     * The scope check is what stops a crafted student id from reaching another
     * family's marks — the permission alone is not enough.
     */
    public function view(User $user, StudentProfile $student): bool
    {
        return $user->can('exam-result.view') && $this->ownsStudent($student->id);
    }
}
