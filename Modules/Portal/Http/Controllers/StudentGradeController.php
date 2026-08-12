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

class StudentGradeController extends BaseInertiaController
{
    public function __construct(private readonly PortalScopeService $scope) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', ExamResult::class);

        /*
         * visibleToFamily() applies both the ownership scope and the
         * published-exams rule, so a student can never read marks the school
         * has not released yet.
         */
        $results = (new ExamResultQuery)
            ->visibleToFamily($this->scope)
            ->forSubject($request->filled('subject_id') ? $request->integer('subject_id') : null)
            ->latestFirst()
            ->with(['exam', 'subject'])
            ->paginate($request->integer(Pagination::PER_PAGE_PARAM, Pagination::DEFAULT_PER_PAGE));

        return $this->render('Portal::Student/Grades', [
            'results' => ExamResultResource::collection($results),
            'filters' => $request->only(['subject_id']),
        ]);
    }
}
