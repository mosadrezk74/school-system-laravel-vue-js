<?php

namespace Modules\Portal\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\Examination\Models\ExamResult;
use Modules\Examination\Queries\ExamResultQuery;
use Modules\Examination\Transformers\ExamResultResource;
use Modules\Portal\Services\PortalScopeService;
use Modules\Portal\Traits\ResolvesGuardianChildren;

class ParentGradeController extends BaseInertiaController
{
    use ResolvesGuardianChildren;

    public function __construct(private readonly PortalScopeService $scope) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', ExamResult::class);

        // selectedChild() resolves against the guardian's own children, so an
        // id from the query string can never reach another family's student.
        $child = $this->selectedChild($request);

        /*
         * visibleToFamily() applies both the ownership scope and the
         * published-exams rule, so a guardian can never read marks the school
         * has not released yet.
         */
        $results = (new ExamResultQuery)
            ->visibleToFamily($this->scope)
            ->forStudent($child?->id)
            ->latestFirst()
            ->with(['exam', 'subject'])
            ->paginate($request->integer(Pagination::PER_PAGE_PARAM, Pagination::DEFAULT_PER_PAGE));

        return $this->render('Portal::Parent/Grades', [
            'results' => ExamResultResource::collection($results),
            'children' => $this->childOptions(),
            'selectedChild' => $child?->id,
        ]);
    }
}
