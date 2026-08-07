<?php

namespace Modules\Portal\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\Examination\Models\ExamResult;
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

        $child = $this->selectedChild($request);

        $results = $this->scope
            ->applyStudentScope(ExamResult::query())
            ->when($child, fn ($query) => $query->where('student_id', $child->id))
            ->with(['exam', 'subject'])
            ->latest('id')
            ->paginate($request->integer(Pagination::PER_PAGE_PARAM, Pagination::DEFAULT_PER_PAGE));

        return $this->render('Portal::Parent/Grades', [
            'results' => ExamResultResource::collection($results),
            'children' => $this->childOptions(),
            'selectedChild' => $child?->id,
        ]);
    }
}
