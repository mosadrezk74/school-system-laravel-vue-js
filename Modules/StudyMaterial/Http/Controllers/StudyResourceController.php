<?php

namespace Modules\StudyMaterial\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Academic\Models\GradeLevel;
use Modules\Auth\Enums\UserRole;
use Modules\Classroom\Models\Classroom;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\StudyMaterial\Actions\ArchiveStudyResourceAction;
use Modules\StudyMaterial\Actions\CreateStudyResourceAction;
use Modules\StudyMaterial\Actions\DownloadStudyResourceAction;
use Modules\StudyMaterial\Actions\RestoreStudyResourceAction;
use Modules\StudyMaterial\Actions\UpdateStudyResourceAction;
use Modules\StudyMaterial\DTOs\CreateStudyResourceDTO;
use Modules\StudyMaterial\DTOs\StudyResourceFilterDTO;
use Modules\StudyMaterial\DTOs\UpdateStudyResourceDTO;
use Modules\StudyMaterial\Enums\StudyResourceType;
use Modules\StudyMaterial\Http\Requests\StoreStudyResourceRequest;
use Modules\StudyMaterial\Http\Requests\UpdateStudyResourceRequest;
use Modules\StudyMaterial\Models\StudyResource;
use Modules\StudyMaterial\Queries\StudyResourceQuery;
use Modules\StudyMaterial\Transformers\StudyResourceResource;
use Modules\Subject\Models\Subject;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StudyResourceController extends BaseInertiaController
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', StudyResource::class);

        $filters = StudyResourceFilterDTO::fromRequest($request);

        $query = (new StudyResourceQuery)
            ->searchTitle($filters->search)
            ->subjectId($filters->subjectId)
            ->gradeLevelId($filters->gradeLevelId)
            ->teacherId($filters->teacherId)
            ->type($filters->type)
            ->ownedBy($this->restrictToOwnTeacherId())
            ->sort($filters->sortBy, $filters->sortOrder);

        $studyResources = $query->paginate($filters->perPage);

        return $this->render('StudyMaterial::StudyResources/Index', [
            'studyResources' => StudyResourceResource::collection($studyResources),
            'filters' => $request->only([
                Pagination::SEARCH_PARAM,
                'subject_id',
                'grade_level_id',
                'teacher_id',
                'type',
            ]),
            ...$this->lookups(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', StudyResource::class);

        return $this->render('StudyMaterial::StudyResources/Create', $this->lookups());
    }

    public function store(StoreStudyResourceRequest $request, CreateStudyResourceAction $action): RedirectResponse
    {
        $this->authorize('create', StudyResource::class);

        $action->execute(CreateStudyResourceDTO::fromRequest($request));

        return $this->toRoute('study-resources.index', [], __('studymaterial::study_resource.created'));
    }

    public function show(StudyResource $studyResource): Response
    {
        $this->authorize('view', $studyResource);

        $studyResource->load(['subject', 'gradeLevel', 'classroom', 'teacher', 'media']);

        return $this->render('StudyMaterial::StudyResources/Show', [
            'studyResource' => new StudyResourceResource($studyResource),
        ]);
    }

    public function edit(StudyResource $studyResource): Response
    {
        $this->authorize('update', $studyResource);

        $studyResource->load(['subject', 'gradeLevel', 'classroom', 'teacher', 'media']);

        return $this->render('StudyMaterial::StudyResources/Edit', [
            'studyResource' => new StudyResourceResource($studyResource),
            ...$this->lookups(),
        ]);
    }

    public function update(UpdateStudyResourceRequest $request, StudyResource $studyResource, UpdateStudyResourceAction $action): RedirectResponse
    {
        $this->authorize('update', $studyResource);

        $action->execute(['studyResource' => $studyResource, 'dto' => UpdateStudyResourceDTO::fromRequest($request)]);

        return $this->toRoute('study-resources.index', [], __('studymaterial::study_resource.updated'));
    }

    public function destroy(StudyResource $studyResource, ArchiveStudyResourceAction $action): RedirectResponse
    {
        $this->authorize('delete', $studyResource);

        $action->execute($studyResource);

        return $this->toRoute('study-resources.index', [], __('studymaterial::study_resource.archived'));
    }

    public function restore(int $studyResource, RestoreStudyResourceAction $action): RedirectResponse
    {
        $studyResource = StudyResource::withTrashed()->findOrFail($studyResource);

        $this->authorize('restore', $studyResource);

        $action->execute($studyResource);

        return $this->toRoute('study-resources.index', [], __('studymaterial::study_resource.restored'));
    }

    public function download(StudyResource $studyResource, DownloadStudyResourceAction $action): BinaryFileResponse
    {
        $this->authorize('download', $studyResource);

        $media = $action->execute($studyResource);

        return response()->download($media->getPath(), $media->file_name);
    }

    private function restrictToOwnTeacherId(): ?int
    {
        $user = auth()->user();

        return $user?->role === UserRole::Teacher ? $user->id : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function lookups(): array
    {
        return [
            'subjects' => Subject::query()->get()->map(fn (Subject $subject) => [
                'id' => $subject->id,
                'name' => $subject->name,
            ])->values(),
            'gradeLevels' => GradeLevel::query()->where('is_active', true)->get()
                ->map(fn (GradeLevel $gradeLevel) => [
                    'id' => $gradeLevel->id,
                    'name' => $gradeLevel->name,
                ])->values(),
            'classrooms' => Classroom::query()->where('is_active', true)->get(['id', 'name']),
            'teachers' => User::query()->where('role', UserRole::Teacher->value)->get(['id', 'name']),
            'types' => StudyResourceType::options(),
        ];
    }
}
