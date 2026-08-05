<?php

namespace Modules\Branch\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Branch\Actions\ArchiveBranchAction;
use Modules\Branch\Actions\CreateBranchAction;
use Modules\Branch\Actions\RestoreBranchAction;
use Modules\Branch\Actions\UpdateBranchAction;
use Modules\Branch\DTOs\BranchFilterDTO;
use Modules\Branch\DTOs\CreateBranchDTO;
use Modules\Branch\DTOs\UpdateBranchDTO;
use Modules\Branch\Http\Requests\StoreBranchRequest;
use Modules\Branch\Http\Requests\UpdateBranchRequest;
use Modules\Branch\Models\Branch;
use Modules\Branch\Queries\BranchQuery;
use Modules\Branch\Transformers\BranchResource;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;

class BranchController extends BaseInertiaController
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Branch::class);

        $filters = BranchFilterDTO::fromRequest($request);

        $query = (new BranchQuery)
            ->search($filters->search)
            ->isActive($filters->isActive)
            ->isMain($filters->isMain)
            ->city($filters->city)
            ->sort($filters->sortBy, $filters->sortOrder);

        $branches = $query->paginate($filters->perPage);

        return Inertia::render('Branch::Branches/Index', [
            'branches' => BranchResource::collection($branches),
            'filters' => $request->only([
                Pagination::SEARCH_PARAM,
                'is_active',
                'is_main',
                'city',
            ]),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Branch::class);

        $managers = \App\Models\User::query()
            ->whereHas('roles', fn($q) => $q->whereIn('name', ['admin', 'manager']))
            ->get(['id', 'name', 'email']);

        return Inertia::render('Branch::Branches/Create', [
            'managers' => $managers,
        ]);
    }

    public function store(StoreBranchRequest $request, CreateBranchAction $action): RedirectResponse
    {
        $this->authorize('create', Branch::class);

        $action->execute(CreateBranchDTO::fromRequest($request));

        return $this->toRoute('branches.index', [], __('branch::branch.created'));
    }

    public function show(Branch $branch): Response
    {
        $this->authorize('view', $branch);

        $branch->load(['manager']);

        return Inertia::render('Branch::Branches/Show', [
            'branch' => new BranchResource($branch),
        ]);
    }

    public function edit(Branch $branch): Response
    {
        $this->authorize('update', $branch);

        $branch->load('manager');

        $managers = \App\Models\User::query()
            ->whereHas('roles', fn($q) => $q->whereIn('name', ['admin', 'manager']))
            ->get(['id', 'name', 'email']);

        return Inertia::render('Branch::Branches/Edit', [
            'branch' => new BranchResource($branch),
            'managers' => $managers,
        ]);
    }

    public function update(UpdateBranchRequest $request, Branch $branch, UpdateBranchAction $action): RedirectResponse
    {
        $this->authorize('update', $branch);

        $action->execute([
            'branch' => $branch,
            'dto' => UpdateBranchDTO::fromRequest($request),
        ]);

        return $this->toRoute('branches.index', [], __('branch::branch.updated'));
    }

    public function destroy(Branch $branch, ArchiveBranchAction $action): RedirectResponse
    {
        $this->authorize('delete', $branch);

        $action->execute($branch);

        return $this->toRoute('branches.index', [], __('branch::branch.archived'));
    }

    public function restore(int $branch, RestoreBranchAction $action): RedirectResponse
    {
        $branch = Branch::withTrashed()->findOrFail($branch);

        $this->authorize('restore', $branch);

        $action->execute($branch);

        return $this->toRoute('branches.index', [], __('branch::branch.restored'));
    }
}