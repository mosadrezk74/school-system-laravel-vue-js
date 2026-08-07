<?php

namespace Modules\Finance\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\Finance\Actions\ArchiveFeeCategoryAction;
use Modules\Finance\Actions\CreateFeeCategoryAction;
use Modules\Finance\Actions\RestoreFeeCategoryAction;
use Modules\Finance\Actions\UpdateFeeCategoryAction;
use Modules\Finance\DTOs\CreateFeeCategoryDTO;
use Modules\Finance\DTOs\FeeCategoryFilterDTO;
use Modules\Finance\DTOs\UpdateFeeCategoryDTO;
use Modules\Finance\Http\Requests\StoreFeeCategoryRequest;
use Modules\Finance\Http\Requests\UpdateFeeCategoryRequest;
use Modules\Finance\Models\FeeCategory;
use Modules\Finance\Queries\FeeCategoryQuery;
use Modules\Finance\Transformers\FeeCategoryResource;

class FeeCategoryController extends BaseInertiaController
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', FeeCategory::class);

        $filters = FeeCategoryFilterDTO::fromRequest($request);

        $feeCategories = (new FeeCategoryQuery)
            ->searchTerm($filters->search)
            ->isActive($filters->isActive)
            ->sort($filters->sortBy, $filters->sortOrder)
            ->paginate($filters->perPage);

        return $this->render('Finance::FeeCategories/Index', [
            'feeCategories' => FeeCategoryResource::collection($feeCategories),
            'filters' => $request->only([Pagination::SEARCH_PARAM, 'is_active']),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', FeeCategory::class);

        return $this->render('Finance::FeeCategories/Create');
    }

    public function store(StoreFeeCategoryRequest $request, CreateFeeCategoryAction $action): RedirectResponse
    {
        $this->authorize('create', FeeCategory::class);

        $action->execute(CreateFeeCategoryDTO::fromRequest($request));

        return $this->toRoute('fee-categories.index', [], __('finance::finance.fee_category_created'));
    }

    public function show(FeeCategory $feeCategory): Response
    {
        $this->authorize('view', $feeCategory);

        return $this->render('Finance::FeeCategories/Show', [
            'feeCategory' => new FeeCategoryResource($feeCategory),
        ]);
    }

    public function edit(FeeCategory $feeCategory): Response
    {
        $this->authorize('update', $feeCategory);

        return $this->render('Finance::FeeCategories/Edit', [
            'feeCategory' => new FeeCategoryResource($feeCategory),
        ]);
    }

    public function update(UpdateFeeCategoryRequest $request, FeeCategory $feeCategory, UpdateFeeCategoryAction $action): RedirectResponse
    {
        $this->authorize('update', $feeCategory);

        $action->execute(['feeCategory' => $feeCategory, 'dto' => UpdateFeeCategoryDTO::fromRequest($request)]);

        return $this->toRoute('fee-categories.index', [], __('finance::finance.fee_category_updated'));
    }

    public function destroy(FeeCategory $feeCategory, ArchiveFeeCategoryAction $action): RedirectResponse
    {
        $this->authorize('delete', $feeCategory);

        $action->execute($feeCategory);

        return $this->toRoute('fee-categories.index', [], __('finance::finance.fee_category_archived'));
    }

    public function restore(int $feeCategory, RestoreFeeCategoryAction $action): RedirectResponse
    {
        $feeCategory = FeeCategory::withTrashed()->findOrFail($feeCategory);

        $this->authorize('restore', $feeCategory);

        $action->execute($feeCategory);

        return $this->toRoute('fee-categories.index', [], __('finance::finance.fee_category_restored'));
    }
}
