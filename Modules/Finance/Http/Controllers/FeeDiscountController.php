<?php

namespace Modules\Finance\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Academic\Models\GradeLevel;
use Modules\Classroom\Models\Classroom;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\Finance\Actions\ArchiveFeeDiscountAction;
use Modules\Finance\Actions\CreateFeeDiscountAction;
use Modules\Finance\Actions\RestoreFeeDiscountAction;
use Modules\Finance\Actions\UpdateFeeDiscountAction;
use Modules\Finance\DTOs\CreateFeeDiscountDTO;
use Modules\Finance\DTOs\FeeDiscountFilterDTO;
use Modules\Finance\DTOs\UpdateFeeDiscountDTO;
use Modules\Finance\Enums\DiscountType;
use Modules\Finance\Http\Requests\StoreFeeDiscountRequest;
use Modules\Finance\Http\Requests\UpdateFeeDiscountRequest;
use Modules\Finance\Models\FeeDiscount;
use Modules\Finance\Queries\FeeDiscountQuery;
use Modules\Finance\Transformers\FeeDiscountResource;

class FeeDiscountController extends BaseInertiaController
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', FeeDiscount::class);

        $filters = FeeDiscountFilterDTO::fromRequest($request);

        $feeDiscounts = (new FeeDiscountQuery)
            ->searchTerm($filters->search)
            ->discountType($filters->discountType)
            ->gradeLevelId($filters->gradeLevelId)
            ->classroomId($filters->classroomId)
            ->isActive($filters->isActive)
            ->sort($filters->sortBy, $filters->sortOrder)
            ->paginate($filters->perPage);

        return $this->render('Finance::FeeDiscounts/Index', [
            'feeDiscounts' => FeeDiscountResource::collection($feeDiscounts),
            'filters' => $request->only([
                Pagination::SEARCH_PARAM,
                'discount_type',
                'grade_level_id',
                'classroom_id',
                'is_active',
            ]),
            ...$this->lookups(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', FeeDiscount::class);

        return $this->render('Finance::FeeDiscounts/Create', $this->lookups());
    }

    public function store(StoreFeeDiscountRequest $request, CreateFeeDiscountAction $action): RedirectResponse
    {
        $this->authorize('create', FeeDiscount::class);

        $action->execute(CreateFeeDiscountDTO::fromRequest($request));

        return $this->toRoute('fee-discounts.index', [], __('finance::finance.fee_discount_created'));
    }

    public function show(FeeDiscount $feeDiscount): Response
    {
        $this->authorize('view', $feeDiscount);

        $feeDiscount->load(['gradeLevel', 'classroom']);

        return $this->render('Finance::FeeDiscounts/Show', [
            'feeDiscount' => new FeeDiscountResource($feeDiscount),
        ]);
    }

    public function edit(FeeDiscount $feeDiscount): Response
    {
        $this->authorize('update', $feeDiscount);

        $feeDiscount->load(['gradeLevel', 'classroom']);

        return $this->render('Finance::FeeDiscounts/Edit', [
            'feeDiscount' => new FeeDiscountResource($feeDiscount),
            ...$this->lookups(),
        ]);
    }

    public function update(UpdateFeeDiscountRequest $request, FeeDiscount $feeDiscount, UpdateFeeDiscountAction $action): RedirectResponse
    {
        $this->authorize('update', $feeDiscount);

        $action->execute(['feeDiscount' => $feeDiscount, 'dto' => UpdateFeeDiscountDTO::fromRequest($request)]);

        return $this->toRoute('fee-discounts.index', [], __('finance::finance.fee_discount_updated'));
    }

    public function destroy(FeeDiscount $feeDiscount, ArchiveFeeDiscountAction $action): RedirectResponse
    {
        $this->authorize('delete', $feeDiscount);

        $action->execute($feeDiscount);

        return $this->toRoute('fee-discounts.index', [], __('finance::finance.fee_discount_archived'));
    }

    public function restore(int $feeDiscount, RestoreFeeDiscountAction $action): RedirectResponse
    {
        $feeDiscount = FeeDiscount::withTrashed()->findOrFail($feeDiscount);

        $this->authorize('restore', $feeDiscount);

        $action->execute($feeDiscount);

        return $this->toRoute('fee-discounts.index', [], __('finance::finance.fee_discount_restored'));
    }

    /**
     * @return array<string, mixed>
     */
    private function lookups(): array
    {
        return [
            'gradeLevels' => GradeLevel::query()->where('is_active', true)->get()
                ->map(fn (GradeLevel $gradeLevel) => [
                    'id' => $gradeLevel->id,
                    'name' => $gradeLevel->name,
                ])->values(),
            'classrooms' => Classroom::query()->where('is_active', true)->get(['id', 'name']),
            'discountTypes' => DiscountType::options(),
        ];
    }
}
