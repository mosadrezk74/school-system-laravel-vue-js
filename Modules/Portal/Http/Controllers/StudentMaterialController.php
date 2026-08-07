<?php

namespace Modules\Portal\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\Portal\Services\PortalScopeService;
use Modules\StudyMaterial\Actions\DownloadStudyResourceAction;
use Modules\StudyMaterial\Enums\StudyResourceType;
use Modules\StudyMaterial\Models\StudyResource;
use Modules\StudyMaterial\Queries\StudyResourceQuery;
use Modules\StudyMaterial\Transformers\StudyResourceResource;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StudentMaterialController extends BaseInertiaController
{
    public function __construct(private readonly PortalScopeService $scope) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', StudyResource::class);

        $resources = (new StudyResourceQuery)
            ->gradeLevelIds($this->scope->gradeLevelIdsFor())
            ->searchTitle($request->string(Pagination::SEARCH_PARAM)->toString() ?: null)
            ->type($request->string('type')->toString() ?: null)
            ->sort('created_at', 'desc')
            ->paginate($request->integer(Pagination::PER_PAGE_PARAM, Pagination::DEFAULT_PER_PAGE));

        return $this->render('Portal::Student/Materials', [
            'resources' => StudyResourceResource::collection($resources),
            'filters' => $request->only([Pagination::SEARCH_PARAM, 'type']),
            'types' => StudyResourceType::options(),
        ]);
    }

    public function download(StudyResource $studyResource, DownloadStudyResourceAction $action): BinaryFileResponse
    {
        $this->authorize('download', $studyResource);

        abort_unless($this->scope->canAccessGradeLevel($studyResource->grade_level_id), 403);

        $media = $action->execute($studyResource);

        return response()->download($media->getPath(), $media->file_name);
    }
}
