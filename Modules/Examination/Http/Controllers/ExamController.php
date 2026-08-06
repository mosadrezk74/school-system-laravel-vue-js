<?php

namespace Modules\Examination\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Academic\Models\AcademicYear;
use Modules\Academic\Models\Semester;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\Examination\Actions\ArchiveExamAction;
use Modules\Examination\Actions\CreateExamAction;
use Modules\Examination\Actions\EnterExamResultsAction;
use Modules\Examination\Actions\RestoreExamAction;
use Modules\Examination\Actions\UpdateExamAction;
use Modules\Examination\DTOs\CreateExamDTO;
use Modules\Examination\DTOs\EnterExamResultsDTO;
use Modules\Examination\DTOs\ExamFilterDTO;
use Modules\Examination\DTOs\UpdateExamDTO;
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

        $filters = ExamFilterDTO::fromRequest($request);

        $query = (new ExamQuery)
            ->searchNameOrCode($filters->search)
            ->academicYearId($filters->academicYearId)
            ->semesterId($filters->semesterId)
            ->classId($filters->classId)
            ->examType($filters->examType)
            ->isPublished($filters->isPublished)
            ->sort($filters->sortBy, $filters->sortOrder);

        $exams = $query->paginate($filters->perPage);

        return $this->render('Examination::Exams/Index', [
            'exams' => ExamResource::collection($exams),
            'filters' => $request->only([
                Pagination::SEARCH_PARAM,
                'academic_year_id',
                'semester_id',
                'class_id',
                'exam_type',
                'is_published',
            ]),
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
            'academicYears' => AcademicYear::query()->where('is_active', true)->get(['id', 'name']),
            'semesters' => Semester::query()->get(['id', 'name', 'academic_year_id']),
            'classes' => SchoolClass::query()->where('is_active', true)
                ->get(['id', 'name', 'section_code', 'academic_year_id']),
            'subjects' => Subject::query()->get(['id', 'name']),
        ];
    }
}
