<?php

namespace Modules\Academic\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Academic\Actions\ArchiveGradeLevelAction;
use Modules\Academic\Actions\CreateGradeLevelAction;
use Modules\Academic\Actions\RestoreGradeLevelAction;
use Modules\Academic\Actions\UpdateGradeLevelAction;
use Modules\Academic\DTOs\CreateGradeLevelDTO;
use Modules\Academic\DTOs\GradeLevelFilterDTO;
use Modules\Academic\DTOs\UpdateGradeLevelDTO;
use Modules\Academic\Http\Requests\StoreGradeLevelRequest;
use Modules\Academic\Http\Requests\UpdateGradeLevelRequest;
use Modules\Academic\Models\GradeLevel;
use Modules\Academic\Queries\GradeLevelQuery;
use Modules\Academic\Transformers\GradeLevelResource;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;

class GradeLevelController extends BaseInertiaController
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', GradeLevel::class);

        $filters = GradeLevelFilterDTO::fromRequest($request);

        $gradeLevels = (new GradeLevelQuery)
            ->withArchived()
            ->search($filters->search)
            ->isActive($filters->isActive)
            ->sort($filters->sortBy, $filters->sortOrder)
            ->paginate($filters->perPage);

        return $this->render('Academic::GradeLevels/Index', [
            'gradeLevels' => GradeLevelResource::collection($gradeLevels),

            // Echoed back so filters survive a reload or a shared link.
            'filters' => $request->only([
                Pagination::SEARCH_PARAM,
                'is_active',
            ]),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', GradeLevel::class);

        return $this->render('Academic::GradeLevels/Create');
    }

    public function store(StoreGradeLevelRequest $request, CreateGradeLevelAction $action): RedirectResponse
    {
        $this->authorize('create', GradeLevel::class);

        $action->execute(CreateGradeLevelDTO::fromRequest($request));

        return $this->toRoute('academic.grade-levels.index', [], __('academic::grade_levels.created'));
    }

    public function show(GradeLevel $gradeLevel): Response
    {
        $this->authorize('view', $gradeLevel);

        return $this->render('Academic::GradeLevels/Show', [
            'gradeLevel' => new GradeLevelResource($gradeLevel),
        ]);
    }

    public function edit(GradeLevel $gradeLevel): Response
    {
        $this->authorize('update', $gradeLevel);

        return $this->render('Academic::GradeLevels/Edit', [
            'gradeLevel' => new GradeLevelResource($gradeLevel),
        ]);
    }

    public function update(
        UpdateGradeLevelRequest $request,
        GradeLevel $gradeLevel,
        UpdateGradeLevelAction $action,
    ): RedirectResponse {
        $this->authorize('update', $gradeLevel);

        $action->execute([
            'gradeLevel' => $gradeLevel,
            'dto' => UpdateGradeLevelDTO::fromRequest($request),
        ]);

        return $this->toRoute('academic.grade-levels.index', [], __('academic::grade_levels.updated'));
    }

    public function destroy(GradeLevel $gradeLevel, ArchiveGradeLevelAction $action): RedirectResponse
    {
        $this->authorize('delete', $gradeLevel);

        $action->execute($gradeLevel);

        return $this->toRoute('academic.grade-levels.index', [], __('academic::grade_levels.archived'));
    }

    public function restore(int $gradeLevel, RestoreGradeLevelAction $action): RedirectResponse
    {
        $gradeLevel = GradeLevel::withTrashed()->findOrFail($gradeLevel);

        $this->authorize('restore', $gradeLevel);

        $action->execute($gradeLevel);

        return $this->toRoute('academic.grade-levels.index', [], __('academic::grade_levels.restored'));
    }
}
