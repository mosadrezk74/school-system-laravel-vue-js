<?php

namespace Modules\Behavior\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Behavior\Actions\CreateBehaviorCategoryAction;
use Modules\Behavior\Actions\DeleteBehaviorCategoryAction;
use Modules\Behavior\Actions\UpdateBehaviorCategoryAction;
use Modules\Behavior\DTOs\BehaviorCategoryDTO;
use Modules\Behavior\Http\Requests\StoreBehaviorCategoryRequest;
use Modules\Behavior\Http\Requests\UpdateBehaviorCategoryRequest;
use Modules\Behavior\Models\BehaviorCategory;
use Modules\Behavior\Queries\BehaviorCategoryQuery;
use Modules\Behavior\Transformers\BehaviorCategoryResource;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\Core\DTOs\ListQueryDTO;

class BehaviorCategoryController extends BaseInertiaController
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', BehaviorCategory::class);

        $filters = ListQueryDTO::fromRequest($request);

        $query = new BehaviorCategoryQuery;

        $categories = $query
            ->search($filters->search)
            ->where($filters->values)
            ->sort($filters->sortBy, $filters->sortOrder)
            ->paginate($filters->perPage);

        return $this->render('Behavior::Categories/Index', [
            'categories' => BehaviorCategoryResource::collection($categories),
            'filters' => [
                Pagination::SEARCH_PARAM => $filters->search,
                ...$query->activeFilters($filters->values),
            ],
            'filterDefinitions' => $query->filterSchema(),
        ]);
    }

    public function store(StoreBehaviorCategoryRequest $request, CreateBehaviorCategoryAction $action): RedirectResponse
    {
        $this->authorize('create', BehaviorCategory::class);

        $action->execute(BehaviorCategoryDTO::fromRequest($request));

        return $this->backWithSuccess(__('behavior::behavior.category_created'));
    }

    public function update(
        UpdateBehaviorCategoryRequest $request,
        BehaviorCategory $category,
        UpdateBehaviorCategoryAction $action,
    ): RedirectResponse {
        $this->authorize('update', $category);

        $action->execute([
            'category' => $category,
            'dto' => BehaviorCategoryDTO::fromRequest($request),
        ]);

        return $this->backWithSuccess(__('behavior::behavior.category_updated'));
    }

    public function destroy(BehaviorCategory $category, DeleteBehaviorCategoryAction $action): RedirectResponse
    {
        $this->authorize('delete', $category);

        $action->execute($category);

        return $this->backWithSuccess(__('behavior::behavior.category_deleted'));
    }
}
