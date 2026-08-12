<?php

namespace Modules\Portal\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Academic\Exceptions\AcademicYearNotSetException;
use Modules\Academic\Exceptions\SemesterNotSetException;
use Modules\Academic\Services\AcademicContextService;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Examination\Queries\ReportCardQuery;
use Modules\Examination\Transformers\ReportCardResource;
use Modules\Portal\Services\PortalScopeService;
use Modules\Portal\Traits\ResolvesGuardianChildren;
use Modules\Student\Models\StudentProfile;

/**
 * The family-facing report card, shared by the student and guardian portals.
 *
 * One controller rather than two: the only difference is whose card is being
 * read, and PortalScopeService already answers that. Both audiences see
 * published results only — publishedOnly() carries the same rule the grade
 * pages use, so a card can never reveal marks the school has not released.
 */
class ReportCardPortalController extends BaseInertiaController
{
    use ResolvesGuardianChildren;

    public function __construct(
        private readonly PortalScopeService $scope,
        private readonly AcademicContextService $context,
    ) {}

    public function show(Request $request, ReportCardQuery $query): Response
    {
        // The portal's own ability: a family holds exam-result.view but may
        // not browse the school, which viewAnyReportCard now refuses them.
        $this->authorize('viewOwnReportCard');

        $student = $this->resolveStudent($request);

        // Authorised per student: for a guardian the id is already resolved
        // against their own children, and this refuses anything else outright.
        if ($student !== null) {
            $this->authorize('viewReportCard', $student);
        }

        return $this->render('Portal::ReportCard', [
            'card' => $this->buildCard($query, $student),
            'children' => $this->scope->isGuardian() ? $this->childOptions() : [],
            'selectedChild' => $this->scope->isGuardian() ? $student?->id : null,
        ]);
    }

    /**
     * A student always reads their own profile; a guardian reads the selected
     * child, matched against their own children so an id from the query string
     * that belongs to another family resolves to null rather than that child.
     */
    private function resolveStudent(Request $request): ?StudentProfile
    {
        if ($this->scope->isGuardian()) {
            return $this->selectedChild($request);
        }

        return $this->scope->currentStudentProfile();
    }

    /**
     * A school that has not set its current year or semester yet leaves the
     * page empty rather than raising an error at a family.
     */
    private function buildCard(ReportCardQuery $query, ?StudentProfile $student): ?ReportCardResource
    {
        if ($student === null) {
            return null;
        }

        try {
            ['academic_year_id' => $yearId, 'semester_id' => $semesterId] = $this->context->requireContext();
        } catch (AcademicYearNotSetException|SemesterNotSetException) {
            return null;
        }

        return new ReportCardResource(
            $query->publishedOnly()->forStudentWithRank($student, $yearId, $semesterId),
        );
    }
}
