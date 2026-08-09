<?php

namespace Modules\Assignment\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Academic\Models\Semester;
use Modules\Academic\Services\AcademicYearService;
use Modules\Assignment\Actions\ArchiveStudyMaterialAction;
use Modules\Assignment\Actions\CreateStudyMaterialAction;
use Modules\Assignment\Actions\RestoreStudyMaterialAction;
use Modules\Assignment\Actions\UpdateStudyMaterialAction;
use Modules\Assignment\DTOs\CreateStudyMaterialDTO;
use Modules\Assignment\DTOs\StudyMaterialFilterDTO;
use Modules\Assignment\DTOs\UpdateStudyMaterialDTO;
use Modules\Assignment\Http\Requests\StoreStudyMaterialRequest;
use Modules\Assignment\Http\Requests\UpdateStudyMaterialRequest;
use Modules\Assignment\Models\StudyMaterial;
use Modules\Assignment\Queries\StudyMaterialQuery;
use Modules\Assignment\Transformers\StudyMaterialResource;
use Modules\Auth\Enums\UserRole;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\SchoolClass\Models\SchoolClass;
use Modules\Subject\Models\Subject;

class StudyMaterialController extends BaseInertiaController
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', StudyMaterial::class);

        $filters = StudyMaterialFilterDTO::fromRequest($request);

        $query = (new StudyMaterialQuery)
            ->searchTitle($filters->search)
            ->classId($filters->classId)
            ->subjectId($filters->subjectId)
            ->teacherId($filters->teacherId)
            ->academicYearId($filters->academicYearId)
            ->semesterId($filters->semesterId)
            ->isPublished($filters->isPublished)
            ->sort($filters->sortBy, $filters->sortOrder);

        $studyMaterials = $query->paginate($filters->perPage);

        return $this->render('Assignment::StudyMaterials/Index', [
            'studyMaterials' => StudyMaterialResource::collection($studyMaterials),
            'filters' => $request->only([
                Pagination::SEARCH_PARAM,
                'class_id',
                'subject_id',
                'teacher_id',
                'semester_id',
                'is_published',
            ]),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', StudyMaterial::class);

        return $this->render('Assignment::StudyMaterials/Create', $this->formOptions());
    }

    public function store(StoreStudyMaterialRequest $request, CreateStudyMaterialAction $action): RedirectResponse
    {
        $this->authorize('create', StudyMaterial::class);

        $action->execute(CreateStudyMaterialDTO::fromRequest($request));

        return $this->toRoute('study-materials.index', [], __('assignment::study_material.created'));
    }

    public function show(StudyMaterial $studyMaterial): Response
    {
        $this->authorize('view', $studyMaterial);

        $studyMaterial->load(['class', 'subject', 'teacher', 'academicYear', 'semester']);

        return $this->render('Assignment::StudyMaterials/Show', [
            'studyMaterial' => new StudyMaterialResource($studyMaterial),
        ]);
    }

    public function edit(StudyMaterial $studyMaterial): Response
    {
        $this->authorize('update', $studyMaterial);

        return $this->render('Assignment::StudyMaterials/Edit', [
            'studyMaterial' => new StudyMaterialResource($studyMaterial),
            ...$this->formOptions(),
        ]);
    }

    public function update(UpdateStudyMaterialRequest $request, StudyMaterial $studyMaterial, UpdateStudyMaterialAction $action): RedirectResponse
    {
        $this->authorize('update', $studyMaterial);

        $action->execute(['studyMaterial' => $studyMaterial, 'dto' => UpdateStudyMaterialDTO::fromRequest($request)]);

        return $this->toRoute('study-materials.index', [], __('assignment::study_material.updated'));
    }

    public function destroy(StudyMaterial $studyMaterial, ArchiveStudyMaterialAction $action): RedirectResponse
    {
        $this->authorize('delete', $studyMaterial);

        $action->execute($studyMaterial);

        return $this->toRoute('study-materials.index', [], __('assignment::study_material.archived'));
    }

    public function restore(int $studyMaterial, RestoreStudyMaterialAction $action): RedirectResponse
    {
        $studyMaterial = StudyMaterial::withTrashed()->findOrFail($studyMaterial);

        $this->authorize('restore', $studyMaterial);

        $action->execute($studyMaterial);

        return $this->toRoute('study-materials.index', [], __('assignment::study_material.restored'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function formOptions(): array
    {
        return [
            'semesters' => Semester::query()->where('academic_year_id', AcademicYearService::id())->get()
                ->map(fn (Semester $semester) => [
                    'id' => $semester->id,
                    'name' => $semester->name,
                    'academic_year_id' => $semester->academic_year_id,
                ])
                ->values(),
            'classes' => SchoolClass::query()->where('is_active', true)->where('academic_year_id', AcademicYearService::id())
                ->get(['id', 'name', 'academic_year_id']),
            'subjects' => Subject::query()->get()->map(fn (Subject $subject) => ['id' => $subject->id, 'name' => $subject->name])->values(),
            'teachers' => User::query()->where('role', UserRole::Teacher->value)->get(['id', 'name']),
        ];
    }
}
