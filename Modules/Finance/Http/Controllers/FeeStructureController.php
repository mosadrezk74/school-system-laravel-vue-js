<?php

namespace Modules\Finance\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Academic\Models\AcademicYear;
use Modules\Academic\Models\GradeLevel;
use Modules\Academic\Models\Semester;
use Modules\Classroom\Models\Classroom;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\Core\DTOs\ListQueryDTO;
use Modules\Core\Support\Filters\FilterOptions;
use Modules\Finance\Actions\ArchiveFeeStructureAction;
use Modules\Finance\Actions\CreateFeeStructureAction;
use Modules\Finance\Actions\RestoreFeeStructureAction;
use Modules\Finance\Actions\UpdateFeeStructureAction;
use Modules\Finance\DTOs\CreateFeeStructureDTO;
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

        $filters = ListQueryDTO::fromRequest($request);

        $query = new FeeStructureQuery;

        $feeStructures = $query
            ->search($filters->search)
            ->where($filters->values)
            ->sort($filters->sortBy, $filters->sortOrder)
            ->paginate($filters->perPage);

        return $this->render('Finance::FeeStructures/Index', [
            'feeStructures' => FeeStructureResource::collection($feeStructures),
            'filters' => [
                Pagination::SEARCH_PARAM => $filters->search,
                ...$query->activeFilters($filters->values),
            ],
            'filterDefinitions' => $query->filterSchema($this->scopeFilterOptions()),
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

        $feeStructure->load(['feeCategory', 'gradeLevel', 'classroom', 'academicYear', 'semester']);

        return $this->render('Finance::FeeStructures/Show', [
            'feeStructure' => new FeeStructureResource($feeStructure),
        ]);
    }

    public function edit(FeeStructure $feeStructure): Response
    {
        $this->authorize('update', $feeStructure);

        $feeStructure->load(['feeCategory', 'gradeLevel', 'classroom', 'academicYear', 'semester']);

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
            'classrooms' => Classroom::query()->where('is_active', true)->get()
                ->map(fn (Classroom $classroom) => ['id' => $classroom->id, 'name' => $classroom->name])
                ->values(),
            'frequencies' => FeeFrequency::options(),

            'academicYears' => AcademicYear::query()->orderByDesc('start_date')->get()
                ->map(fn (AcademicYear $year) => [
                    'id' => $year->id,
                    'name' => $year->name,
                    'start_date' => $year->start_date?->toDateString(),
                    'end_date' => $year->end_date?->toDateString(),
                ])->values(),

            /*
             * Semesters carry their year and their dates: the year lets the
             * form narrow the list to the selected year, and the dates are
             * what the period preview is built from — the duration is never
             * stored, only derived.
             */
            'semesters' => Semester::query()->orderBy('start_date')->get()
                ->map(fn (Semester $semester) => [
                    'id' => $semester->id,
                    'name' => $semester->name,
                    'academic_year_id' => $semester->academic_year_id,
                    'start_date' => $semester->start_date?->toDateString(),
                    'end_date' => $semester->end_date?->toDateString(),
                ])->values(),
        ];
    }

    /**
     * The scope pickers a fee structure is narrowed by. Both are nullable on
     * the table — a structure can apply school-wide — so each list carries the
     * "not set" choice.
     *
     * @return array<string, array<int, array{title: string, value: mixed}>>
     */
    private function scopeFilterOptions(): array
    {
        return [
            'academic_year_id' => FilterOptions::withNotSet(
                FilterOptions::fromModels(AcademicYear::query()->orderByDesc('start_date')->get()),
            ),
            'semester_id' => FilterOptions::withNotSet(
                FilterOptions::fromModels(Semester::query()->get()),
            ),
            'fee_category_id' => FilterOptions::fromModels(
                FeeCategory::query()->where('is_active', true)->get(),
            ),
            'grade_level_id' => FilterOptions::withNotSet(
                FilterOptions::fromModels(GradeLevel::query()->where('is_active', true)->get()),
            ),
            'classroom_id' => FilterOptions::withNotSet(
                FilterOptions::fromModels(Classroom::query()->where('is_active', true)->get()),
            ),
        ];
    }
}
