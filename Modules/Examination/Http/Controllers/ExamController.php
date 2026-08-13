<?php

namespace Modules\Examination\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Academic\Models\AcademicYear;
use Modules\Academic\Models\Semester;
use Modules\Academic\Services\AcademicContextService;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\Core\DTOs\ListQueryDTO;
use Modules\Core\Support\Filters\FilterOptions;
use Modules\Examination\Actions\ArchiveExamAction;
use Modules\Examination\Actions\CreateExamAction;
use Modules\Examination\Actions\EnterExamResultsAction;
use Modules\Examination\Actions\RestoreExamAction;
use Modules\Examination\Actions\UpdateExamAction;
use Modules\Examination\DTOs\CreateExamDTO;
use Modules\Examination\DTOs\EnterExamResultsDTO;
use Modules\Examination\DTOs\UpdateExamDTO;
use Modules\Examination\Enums\ExamType;
use Modules\Examination\Http\Requests\StoreExamRequest;
use Modules\Examination\Http\Requests\StoreExamResultsRequest;
use Modules\Examination\Http\Requests\UpdateExamRequest;
use Modules\Examination\Models\Exam;
use Modules\Examination\Models\ExamSubject;
use Modules\Examination\Queries\ExamQuery;
use Modules\Examination\Transformers\ExamResource;
use Modules\Examination\Transformers\ExamSubjectResource;
use Modules\SchoolClass\Models\SchoolClass;
use Modules\Student\Models\StudentProfile;
use Modules\Subject\Models\Subject;

class ExamController extends BaseInertiaController
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Exam::class);

        $filters = ListQueryDTO::fromRequest($request, [
            'academic_year_id' => AcademicContextService::yearId(),
        ]);

        $query = new ExamQuery;

        $exams = $query
            ->search($filters->search)
            ->where($filters->values)
            ->sort($filters->sortBy, $filters->sortOrder)
            ->paginate($filters->perPage);

        return $this->render('Examination::Exams/Index', [
            'exams' => ExamResource::collection($exams),
            'filters' => [
                Pagination::SEARCH_PARAM => $filters->search,
                ...$query->activeFilters($filters->values),
            ],
            'filterDefinitions' => $query->filterSchema($this->filterOptions()),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Exam::class);

        return $this->render('Examination::Exams/Create', $this->formOptions());
    }

    public function store(StoreExamRequest $request, CreateExamAction $action): RedirectResponse
    {
        $this->authorize('create', Exam::class);

        $action->execute(CreateExamDTO::fromRequest($request));

        return $this->toRoute('exams.index', [], __('examination::exam.created'));
    }

    public function show(Exam $exam): Response
    {
        $this->authorize('view', $exam);

        $exam->load(['academicYear', 'semester', 'class', 'examSubjects.subject', 'examSubjects.class']);

        return $this->render('Examination::Exams/Show', [
            'exam' => new ExamResource($exam),
        ]);
    }

    public function edit(Exam $exam): Response
    {
        $this->authorize('update', $exam);

        $exam->load(['examSubjects.subject', 'examSubjects.class']);

        return $this->render('Examination::Exams/Edit', [
            'exam' => new ExamResource($exam),
            ...$this->formOptions(),
        ]);
    }

    public function update(UpdateExamRequest $request, Exam $exam, UpdateExamAction $action): RedirectResponse
    {
        $this->authorize('update', $exam);

        $action->execute(['exam' => $exam, 'dto' => UpdateExamDTO::fromRequest($request)]);

        return $this->toRoute('exams.index', [], __('examination::exam.updated'));
    }

    public function destroy(Exam $exam, ArchiveExamAction $action): RedirectResponse
    {
        $this->authorize('delete', $exam);

        $action->execute($exam);

        return $this->toRoute('exams.index', [], __('examination::exam.archived'));
    }

    public function restore(int $exam, RestoreExamAction $action): RedirectResponse
    {
        $exam = Exam::withTrashed()->findOrFail($exam);

        $this->authorize('restore', $exam);

        $action->execute($exam);

        return $this->toRoute('exams.index', [], __('examination::exam.restored'));
    }

    public function createResults(Exam $exam, ExamSubject $examSubject): Response
    {
        $this->authorize('update', $exam);

        $examSubject->load('subject', 'class');

        $existing = $exam->results()
            ->where('subject_id', $examSubject->subject_id)
            ->where('class_id', $examSubject->class_id)
            ->get()
            ->keyBy('student_id');

        $roster = StudentProfile::query()
            ->where('current_class_id', $examSubject->class_id)
            ->where('is_active', true)
            ->with('user')
            ->get()
            ->map(fn (StudentProfile $student) => [
                'student_id' => $student->id,
                'name' => $student->user?->name,
                'admission_number' => $student->admission_number,
                'obtained_marks' => $existing->get($student->id)?->obtained_marks,
                'remarks' => $existing->get($student->id)?->remarks,
                'percentage' => $existing->get($student->id)?->percentage,
                'is_passed' => $existing->get($student->id)?->is_passed,
                'grade' => $existing->get($student->id)?->grade,
            ])
            ->values();

        return $this->render('Examination::Exams/EnterResults', [
            'exam' => new ExamResource($exam),
            'examSubject' => new ExamSubjectResource($examSubject),
            'roster' => $roster,
        ]);
    }

    public function storeResults(StoreExamResultsRequest $request, Exam $exam, ExamSubject $examSubject, EnterExamResultsAction $action): RedirectResponse
    {
        $this->authorize('update', $exam);

        $action->execute(EnterExamResultsDTO::fromRequest($request));

        return $this->toRoute('exams.show', ['exam' => $exam->id], __('examination::exam.results_entered'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function formOptions(): array
    {
        return [
            'semesters' => Semester::query()->where('academic_year_id', AcademicContextService::yearId())->get()
                ->map(fn (Semester $semester) => [
                    'id' => $semester->id,
                    'name' => $semester->name,
                    'academic_year_id' => $semester->academic_year_id,
                ])
                ->values(),
            'classes' => SchoolClass::query()->where('is_active', true)
                ->get(['id', 'name']),
            'subjects' => Subject::query()->get()->map(fn (Subject $subject) => ['id' => $subject->id, 'name' => $subject->name])->values(),

            /*
             * From the same enum the rule validates against. The form used to
             * keep its own hard-coded list, which had drifted: it offered
             * `unit_test` and no practical or oral option at all.
             */
            'examTypes' => ExamType::selectOptions(),
        ];
    }

    /**
     * Option lists for the filter bar's selects.
     *
     * @return array<string, array<int, array{title: string, value: mixed}>>
     */
    private function filterOptions(): array
    {
        return [
            'academic_year_id' => FilterOptions::fromModels(
                AcademicYear::query()->orderByDesc('start_date')->get(),
            ),
            'semester_id' => FilterOptions::withNotSet(
                FilterOptions::fromModels(Semester::query()->get()),
            ),
            'class_id' => FilterOptions::withNotSet(
                FilterOptions::fromModels(
                    SchoolClass::query()->where('is_active', true)->orderBy('name')->get(),
                ),
            ),
        ];
    }
}
