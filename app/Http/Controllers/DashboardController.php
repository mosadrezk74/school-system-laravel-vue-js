<?php

namespace App\Http\Controllers;

use Inertia\Response;
use Modules\Academic\Models\AcademicYear;
use Modules\Academic\Services\AcademicContextService;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Services\DashboardOverviewService;

class DashboardController extends BaseInertiaController
{
    public function __construct(
        private readonly DashboardOverviewService $overview,
        private readonly AcademicContextService $context,
    ) {}

    /**
     * School overview.
     *
     * Every figure on the page is queried live and scoped to the current
     * academic year. Sections the signed-in user may not see are omitted from
     * the payload entirely rather than hidden in the client.
     */
    public function index(): Response
    {
        $academicYear = $this->context->currentYear();

        return $this->render('Dashboard', [
            'academicYear' => $academicYear instanceof AcademicYear ? [
                'id' => $academicYear->id,
                'name' => $academicYear->name,
                'startDate' => $academicYear->start_date?->toDateString(),
                'endDate' => $academicYear->end_date?->toDateString(),
            ] : null,
            'overview' => $this->overview->build($academicYear?->id),
        ]);
    }
}
