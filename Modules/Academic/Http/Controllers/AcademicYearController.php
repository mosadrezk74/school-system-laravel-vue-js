<?php

namespace Modules\Academic\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Academic\Actions\CreateAcademicYearAction;
use Modules\Academic\Actions\DeleteAcademicYearAction;
use Modules\Academic\Actions\SetCurrentAcademicYearAction;
use Modules\Academic\Actions\UpdateAcademicYearAction;
use Modules\Academic\DTOs\AcademicYearDTO;
use Modules\Academic\Http\Requests\StoreAcademicYearRequest;
use Modules\Academic\Http\Requests\UpdateAcademicYearRequest;
use Modules\Academic\Models\AcademicYear;
use Modules\Academic\Queries\AcademicYearQuery;
use Modules\Academic\Transformers\AcademicYearResource;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\Core\DTOs\ListQueryDTO;

class AcademicYearController extends BaseInertiaController
{
    public function index(Request $request): Response
    {
        $filters = ListQueryDTO::fromRequest($request);

        $query = new AcademicYearQuery;

        $years = $query
            ->search($filters->search)
            ->where($filters->values)
            ->sort($filters->sortBy, $filters->sortOrder)
            ->paginate($filters->perPage);

        return $this->render('Academic::AcademicYears/Index', [
            'years' => AcademicYearResource::collection($years),

            // Echoed back so filters survive a reload or a shared link.
            'filters' => [
                Pagination::SEARCH_PARAM => $filters->search,
                ...$query->activeFilters($filters->values),
            ],
            'filterDefinitions' => $query->filterSchema(),
        ]);
    }

    public function store(
        StoreAcademicYearRequest $request,
        CreateAcademicYearAction $action,
    ): RedirectResponse {
        $action->execute(AcademicYearDTO::fromRequest($request));

        return $this->backWithSuccess(__('academic::academic_years.created'));
    }

    public function update(
        UpdateAcademicYearRequest $request,
        AcademicYear $academicYear,
        UpdateAcademicYearAction $action,
    ): RedirectResponse {
        $action->execute([
            'year' => $academicYear,
            'dto' => AcademicYearDTO::fromRequest($request),
        ]);

        return $this->backWithSuccess(__('academic::academic_years.updated'));
    }

    public function destroy(
        AcademicYear $academicYear,
        DeleteAcademicYearAction $action,
    ): RedirectResponse {
        $action->execute($academicYear);

        return $this->backWithSuccess(__('academic::academic_years.deleted'));
    }

    public function setCurrent(
        AcademicYear $academicYear,
        SetCurrentAcademicYearAction $action,
    ): RedirectResponse {
        $action->execute($academicYear);

        return $this->backWithSuccess(__('academic::academic_years.set_current'));
    }
}
