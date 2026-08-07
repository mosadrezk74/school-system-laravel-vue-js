<?php

namespace Modules\Portal\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Academic\Models\GradeLevel;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\Portal\Http\Requests\TeacherStoreStudyResourceRequest;
use Modules\Portal\Http\Requests\TeacherUpdateStudyResourceRequest;
use Modules\Portal\Services\PortalScopeService;
use Modules\SchoolClass\Models\ClassSubject;
use Modules\StudyMaterial\Actions\ArchiveStudyResourceAction;
use Modules\StudyMaterial\Actions\CreateStudyResourceAction;
use Modules\StudyMaterial\Actions\DownloadStudyResourceAction;
use Modules\StudyMaterial\Actions\UpdateStudyResourceAction;
use Modules\StudyMaterial\DTOs\CreateStudyResourceDTO;
use Modules\StudyMaterial\DTOs\UpdateStudyResourceDTO;
use Modules\StudyMaterial\Enums\StudyResourceType;
use Modules\StudyMaterial\Models\StudyResource;
use Modules\StudyMaterial\Queries\StudyResourceQuery;
use Modules\StudyMaterial\Transformers\StudyResourceResource;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TeacherStudyMaterialController extends BaseInertiaController
{
    public function __construct(private readonly PortalScopeService $scope) {}

    public function index(Request $request): Response
    {
        $resources = (new StudyResourceQuery)
            ->ownedBy($this->scope->teacherUserId())
            ->searchTitle($request->string(Pagination::SEARCH_PARAM)->toString() ?: null)
            ->type($request->string('type')->toString() ?: null)
            ->sort('created_at', 'desc')
            ->paginate($request->integer(Pagination::PER_PAGE_PARAM, Pagination::DEFAULT_PER_PAGE));

        return $this->render('Portal::Teacher/StudyMaterials', [
            'resources' => StudyResourceResource::collection($resources),
            'filters' => $request->only([Pagination::SEARCH_PARAM, 'type']),
            ...$this->lookups(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', StudyResource::class);

        return $this->render('Portal::Teacher/StudyMaterialForm', $this->lookups());
    }

    public function store(TeacherStoreStudyResourceRequest $request, CreateStudyResourceAction $action): RedirectResponse
    {
        $this->authorize('create', StudyResource::class);

        $action->execute(CreateStudyResourceDTO::fromRequest($request));

        return $this->toRoute('teacher.study-materials', [], __('studymaterial::study_resource.created'));
    }

    public function edit(StudyResource $studyResource): Response
    {
        $this->authorize('update', $studyResource);

        $studyResource->load(['subject', 'gradeLevel', 'classroom', 'media']);

        return $this->render('Portal::Teacher/StudyMaterialForm', [
            'studyResource' => new StudyResourceResource($studyResource),
            ...$this->lookups(),
        ]);
    }

    public function update(TeacherUpdateStudyResourceRequest $request, StudyResource $studyResource, UpdateStudyResourceAction $action): RedirectResponse
    {
        $this->authorize('update', $studyResource);

        $action->execute(['studyResource' => $studyResource, 'dto' => UpdateStudyResourceDTO::fromRequest($request)]);

        return $this->toRoute('teacher.study-materials', [], __('studymaterial::study_resource.updated'));
    }

    public function destroy(StudyResource $studyResource, ArchiveStudyResourceAction $action): RedirectResponse
    {
        $this->authorize('delete', $studyResource);

        $action->execute($studyResource);

        return $this->toRoute('teacher.study-materials', [], __('studymaterial::study_resource.archived'));
    }

    public function download(StudyResource $studyResource, DownloadStudyResourceAction $action): BinaryFileResponse
    {
        $this->authorize('download', $studyResource);

        $media = $action->execute($studyResource);

        return response()->download($media->getPath(), $media->file_name);
    }

    /**
     * @return array<string, mixed>
     */
    private function lookups(): array
    {
        return [
            'subjects' => $this->scope->teacherSubjects()
                ->map(fn (ClassSubject $link): array => [
                    'id' => $link->subject_id,
                    'name' => $link->subject?->name,
                ])
                ->unique('id')
                ->values()
                ->all(),
            'gradeLevels' => GradeLevel::query()->where('is_active', true)->get()
                ->map(fn (GradeLevel $level): array => ['id' => $level->id, 'name' => $level->name])
                ->values()
                ->all(),
            'types' => StudyResourceType::options(),
        ];
    }
}
