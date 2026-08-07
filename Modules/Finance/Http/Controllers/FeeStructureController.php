<?php

namespace Modules\Finance\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Academic\Models\GradeLevel;
use Modules\Classroom\Models\Classroom;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\Finance\Actions\ArchiveFeeStructureAction;
use Modules\Finance\Actions\CreateFeeStructureAction;
use Modules\Finance\Actions\RestoreFeeStructureAction;
use Modules\Finance\Actions\UpdateFeeStructureAction;
use Modules\Finance\DTOs\CreateFeeStructureDTO;
use Modules\Finance\DTOs\FeeStructureFilterDTO;
use Modules\Finance\DTOs\UpdateFeeStructureDTO;
use Modules\Finance\Enums\FeeFrequency;
use Modules\Finance\Http\Requests\StoreFeeStructureRequest;
use Modules\Finance\Http\Requests\UpdateFeeStructureRequest;
use Modules\Finance\Models\FeeCategory;
use Modules\Finance\Models\FeeStructure;
use Modules\Finance\Queries\FeeStructureQuery;
use Modules\Finance\Transformers\FeeStructureResource;

class FeeStructureController extends BaseInertiaController
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', FeeStructure::class);

        $filters = FeeStructureFilterDTO::fromRequest($request);

        $feeStructures = (new FeeStructureQuery)
            ->searchName($filters->search)
            ->feeCategoryId($filters->feeCategoryId)
            ->gradeLevelId($filters->gradeLevelId)
            ->classroomId($filters->classroomId)
            ->frequency($filters->frequency)
            ->isOptional($filters->isOptional)
            ->sort($filters->sortBy, $filters->sortOrder)
            ->paginate($filters->perPage);

        return $this->render('Finance::FeeStructures/Index', [
            'feeStructures' => FeeStructureResource::collection($feeStructures),
            'filters' => $request->only([
                Pagination::SEARCH_PARAM,
                'fee_category_id',
                'grade_level_id',
                'classroom_id',
                'frequency',
                'is_optional',
            ]),
            ...$this->lookups(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', FeeStructure::class);

        return $this->render('Finance::FeeStructures/Create', $this->lookups());
    }

    public function store(StoreFeeStructureRequest $request, CreateFeeStructureAction $action): RedirectResponse
    {
        $this->authorize('create', FeeStructure::class);

        $action->execute(CreateFeeStructureDTO::fromRequest($request));

        return $this->toRoute('fee-structures.index', [], __('finance::finance.fee_structure_created'));
    }

    public function show(FeeStructure $feeStructure): Response
    {
        $this->authorize('view', $feeStructure);

        $feeStructure->load(['feeCategory', 'gradeLevel', 'classroom']);

        return $this->render('Finance::FeeStructures/Show', [
            'feeStructure' => new FeeStructureResource($feeStructure),
        ]);
    }

    public function edit(FeeStructure $feeStructure): Response
    {
        $this->authorize('update', $feeStructure);

        $feeStructure->load(['feeCategory', 'gradeLevel', 'classroom']);

        return $this->render('Finance::FeeStructures/Edit', [
            'feeStructure' => new FeeStructureResource($feeStructure),
            ...$this->lookups(),
        ]);
    }

    public function update(UpdateFeeStructureRequest $request, FeeStructure $feeStructure, UpdateFeeStructureAction $action): RedirectResponse
    {
        $this->authorize('update', $feeStructure);

        $action->execute(['feeStructure' => $feeStructure, 'dto' => UpdateFeeStructureDTO::fromRequest($request)]);

        return $this->toRoute('fee-structures.index', [], __('finance::finance.fee_structure_updated'));
    }

    public function destroy(FeeStructure $feeStructure, ArchiveFeeStructureAction $action): RedirectResponse
    {
        $this->authorize('delete', $feeStructure);

        $action->execute($feeStructure);

        return $this->toRoute('fee-structures.index', [], __('finance::finance.fee_structure_archived'));
    }

    public function restore(int $feeStructure, RestoreFeeStructureAction $action): RedirectResponse
    {
        $feeStructure = FeeStructure::withTrashed()->findOrFail($feeStructure);

        $this->authorize('restore', $feeStructure);

        $action->execute($feeStructure);

        return $this->toRoute('fee-structures.index', [], __('finance::finance.fee_structure_restored'));
    }

    /**
     * @return array<string, mixed>
     */
    private function lookups(): array
    {
        return [
            'feeCategories' => FeeCategory::query()->where('is_active', true)->get()
                ->map(fn (FeeCategory $feeCategory) => [
                    'id' => $feeCategory->id,
                    'name' => $feeCategory->name,
                ])->values(),
            'gradeLevels' => GradeLevel::query()->where('is_active', true)->get()
                ->map(fn (GradeLevel $gradeLevel) => [
                    'id' => $gradeLevel->id,
                    'name' => $gradeLevel->name,
                ])->values(),
            'classrooms' => Classroom::query()->where('is_active', true)->get(['id', 'name']),
            'frequencies' => FeeFrequency::options(),
        ];
    }
}
