<?php

namespace Modules\Examination\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Academic\Services\AcademicContextService;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Examination\DTOs\ReportCardFilterDTO;
use Modules\Examination\Queries\ReportCardQuery;
use Modules\Examination\Transformers\ReportCardResource;
use Modules\Portal\Services\PortalScopeService;
use Modules\SchoolClass\Models\SchoolClass;
use Modules\Student\Models\StudentProfile;

/**
 * The staff-facing report card.
 *
 * Staff see every result, published or not — reviewing marks before releasing
 * them is the point of the publishing workflow. A teacher reaches only their
 * own students, which PortalScopeService decides, not this controller.
 */
class ReportCardController extends BaseInertiaController
{
    public function __construct(
        private readonly PortalScopeService $scope,
        private readonly AcademicContextService $context,
    ) {}

    public function index(Request $request, ReportCardQuery $query): Response
    {
        $this->authorize('viewAnyReportCard');

        // The current year and semester come from the academic context, never
        // from the query string.
        ['academic_year_id' => $yearId, 'semester_id' => $semesterId] = $this->context->requireContext();

        $filters = ReportCardFilterDTO::fromRequest($request);
        $student = $this->resolveStudent($filters);

        return $this->render('Examination::ReportCards/Index', [
            'card' => $student !== null
                ? new ReportCardResource($query->forStudentWithRank($student, $yearId, $semesterId))
                : null,
            'students' => $this->studentOptions($filters->classId),
            'classes' => $this->classOptions($yearId),
            'filters' => [
                'student_id' => $student?->id,
                'class_id' => $filters->classId,
            ],
            'context' => [
                'academic_year_id' => $yearId,
                'semester_id' => $semesterId,
            ],
        ]);
    }

    /**
     * Resolves the requested student and authorises them individually, so a
     * teacher cannot read a card for a class they do not teach by passing its
     * student id.
     */
    private function resolveStudent(ReportCardFilterDTO $filters): ?StudentProfile
    {
        if ($filters->studentId === null) {
            return null;
        }

        $student = StudentProfile::query()->with('user')->find($filters->studentId);

        if ($student === null) {
            return null;
        }

        $this->authorize('viewReportCard', $student);

        return $student;
    }

    /**
     * @return array<int, array{value: int, title: string}>
     */
    private function studentOptions(?int $classId): array
    {
        return $this->scope
            ->applyStudentScope(StudentProfile::query(), 'id')
            ->with('user')
            ->when($classId !== null, fn ($query) => $query->where('current_class_id', $classId))
            ->get()
            ->map(fn (StudentProfile $student): array => [
                'value' => $student->id,
                'title' => (string) $student->user?->name,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{value: int, title: string}>
     */
    private function classOptions(int $academicYearId): array
    {
        return $this->scope
            ->applyClassScope(SchoolClass::query(), 'id')
            ->get()
            ->map(fn (SchoolClass $class): array => [
                'value' => $class->id,
                'title' => (string) $class->name,
            ])
            ->values()
            ->all();
    }
}
