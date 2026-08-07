<?php

namespace Modules\Portal\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Examination\Models\ExamResult;
use Modules\Examination\Transformers\ExamResultResource;
use Modules\Portal\Services\PortalScopeService;

class StudentGradeController extends BaseInertiaController
{
    public function __construct(private readonly PortalScopeService $scope) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', ExamResult::class);

        $results = $this->scope
            ->applyStudentScope(ExamResult::query())
            ->with(['exam', 'subject'])
            ->when($request->filled('subject_id'), fn ($query) => $query->where('subject_id', (int) $request->input('subject_id')))
            ->latest('id')
            ->paginate($request->integer('per_page', 15));

        return $this->render('Portal::Student/Grades', [
            'results' => ExamResultResource::collection($results),
            'filters' => $request->only(['subject_id']),
        ]);
    }
}
