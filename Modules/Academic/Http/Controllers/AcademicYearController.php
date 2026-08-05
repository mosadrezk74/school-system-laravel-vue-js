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

class AcademicYearController extends BaseInertiaController
{
    public function index(Request $request): Response
    {
        $query = new AcademicYearQuery();

        if ($search = $request->string(Pagination::SEARCH_PARAM)->toString()) {
            $query->search($search);
        }

        $query->sort(
            $request->string(Pagination::SORT_PARAM, Pagination::DEFAULT_SORT)->toString(),
            $request->string(Pagination::ORDER_PARAM, Pagination::DEFAULT_ORDER)->toString(),
        );

        $years = $query->paginate(
            $request->integer(Pagination::PER_PAGE_PARAM, Pagination::DEFAULT_PER_PAGE),
        );

        return $this->render('Academic::AcademicYears/Index', [
            'years' => AcademicYearResource::collection($years),

            // Echoed back so the search box survives a reload or shared link.
            'filters' => $request->only([Pagination::SEARCH_PARAM]),
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
