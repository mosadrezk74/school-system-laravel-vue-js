<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Response;
use Modules\Academic\Models\AcademicYear;
use Modules\Academic\Transformers\AcademicYearResource;
use Modules\Core\Abstracts\BaseInertiaController;

class DashboardController extends BaseInertiaController
{
    /**
     * School overview. Every figure is queried live; modules that haven't been
     * built yet report null so the page can show an honest empty state rather
     * than a fabricated number.
     */
    public function index(): Response
    {
        $currentYear = AcademicYear::query()->current()->first();

        return $this->render('Dashboard', [
            'stats' => [
                'students' => null,
                'teachers' => null,
                'staff' => User::query()->count(),
                'attendance' => null,
                'outstandingFees' => null,
                'openIncidents' => null,
            ],
            'currentYear' => $currentYear
                ? new AcademicYearResource($currentYear)
                : null,
        ]);
    }
}
