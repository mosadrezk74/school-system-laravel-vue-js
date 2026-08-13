<?php

namespace Modules\Examination\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Academic\Models\GradeLevel;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\Core\DTOs\ListQueryDTO;
use Modules\Core\Support\Filters\FilterOptions;
use Modules\Examination\Actions\CreateExamGradeAction;
use Modules\Examination\Actions\DeleteExamGradeAction;
use Modules\Examination\Actions\UpdateExamGradeAction;
use Modules\Examination\DTOs\CreateExamGradeDTO;
use Modules\Examination\DTOs\UpdateExamGradeDTO;
use Modules\Examination\Http\Requests\StoreExamGradeRequest;
use Modules\Examination\Http\Requests\UpdateExamGradeRequest;
use Modules\Examination\Models\ExamGrade;
use Modules\Examination\Queries\ExamGradeQuery;
use Modules\Examination\Transformers\ExamGradeResource;
use Modules\Subject\Models\Subject;

class ExamGradeController extends BaseInertiaController
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', ExamGrade::class);

        $filters = ListQueryDTO::fromRequest($request);

        $query = new ExamGradeQuery;

        $grades = $query
            ->search($filters->search)
            ->where($filters->values)
            ->sort($filters->sortBy, $filters->sortOrder)
            ->paginate($filters->perPage);

        return $this->render('Examination::ExamGrades/Index', [
            'rows' => ExamGradeResource::collection($grades),
            'filters' => [
                Pagination::SEARCH_PARAM => $filters->search,
                ...$query->activeFilters($filters->values),
            ],
            'filterDefinitions' => $query->filterSchema([
                'grade_level_id' => FilterOptions::fromModels(GradeLevel::query()->get()),
                'subject_id' => FilterOptions::withNotSet(
                    FilterOptions::fromModels(Subject::query()->get()),
                ),
            ]),
            'gradeLevels' => GradeLevel::query()->get()->map(fn (GradeLevel $gradeLevel) => ['id' => $gradeLevel->id, 'name' => $gradeLevel->name])->values(),
            'subjects' => Subject::query()->get()->map(fn (Subject $subject) => ['id' => $subject->id, 'name' => $subject->name])->values(),
        ]);
    }

    public function store(StoreExamGradeRequest $request, CreateExamGradeAction $action): RedirectResponse
    {
        $this->authorize('create', ExamGrade::class);

        $action->execute(CreateExamGradeDTO::fromRequest($request));

        return $this->backWithSuccess(__('examination::exam_grade.created'));
    }

    public function update(UpdateExamGradeRequest $request, ExamGrade $examGrade, UpdateExamGradeAction $action): RedirectResponse
    {
        $this->authorize('update', $examGrade);

        $action->execute(['examGrade' => $examGrade, 'dto' => UpdateExamGradeDTO::fromRequest($request)]);

        return $this->backWithSuccess(__('examination::exam_grade.updated'));
    }

    public function destroy(ExamGrade $examGrade, DeleteExamGradeAction $action): RedirectResponse
    {
        $this->authorize('delete', $examGrade);

        $action->execute($examGrade);

        return $this->backWithSuccess(__('examination::exam_grade.deleted'));
    }
}
