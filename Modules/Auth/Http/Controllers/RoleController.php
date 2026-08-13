<?php

namespace Modules\Auth\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Auth\Actions\CreateRoleAction;
use Modules\Auth\Actions\DeleteRoleAction;
use Modules\Auth\Actions\UpdateRoleAction;
use Modules\Auth\DTOs\CreateRoleDTO;
use Modules\Auth\DTOs\UpdateRoleDTO;
use Modules\Auth\Http\Requests\StoreRoleRequest;
use Modules\Auth\Http\Requests\UpdateRoleRequest;
use Modules\Auth\Models\Permission;
use Modules\Auth\Models\Role;
use Modules\Auth\Queries\RoleQuery;
use Modules\Auth\Transformers\PermissionResource;
use Modules\Auth\Transformers\RoleResource;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\Core\DTOs\ListQueryDTO;

class RoleController extends BaseInertiaController
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Role::class);

        $filters = ListQueryDTO::fromRequest($request);

        $query = (new RoleQuery)->withCounts();

        $roles = $query
            ->search($filters->search)
            ->where($filters->values)
            ->sort($filters->sortBy, $filters->sortOrder)
            ->paginate($filters->perPage);

        return $this->render('Auth::Roles/Index', [
            'roles' => RoleResource::collection($roles),
            'filters' => [
                Pagination::SEARCH_PARAM => $filters->search,
                ...$query->activeFilters($filters->values),
            ],
            'filterDefinitions' => $query->filterSchema(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Role::class);

        return $this->render('Auth::Roles/Create', [
            'permissions' => $this->groupedPermissions(),
        ]);
    }

    public function store(StoreRoleRequest $request, CreateRoleAction $action): RedirectResponse
    {
        $this->authorize('create', Role::class);

        $action->execute(CreateRoleDTO::fromRequest($request));

        return $this->toRoute('auth.roles.index', [], __('auth::roles.created'));
    }

    public function edit(Role $role): Response
    {
        $this->authorize('update', $role);

        return $this->render('Auth::Roles/Edit', [
            'role' => new RoleResource($role->load('permissions')),
            'permissions' => $this->groupedPermissions(),
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role, UpdateRoleAction $action): RedirectResponse
    {
        $this->authorize('update', $role);

        $action->execute([
            'role' => $role,
            'dto' => UpdateRoleDTO::fromRequest($request),
        ]);

        return $this->toRoute('auth.roles.index', [], __('auth::roles.updated'));
    }

    public function destroy(Role $role, DeleteRoleAction $action): RedirectResponse
    {
        $this->authorize('delete', $role);

        $action->execute($role);

        return $this->toRoute('auth.roles.index', [], __('auth::roles.deleted'));
    }

    /**
     * Permissions grouped by the `module` column the schema already carries,
     * so the picker renders one panel per module instead of a flat list of
     * roughly two hundred checkboxes.
     *
     * @return array<int, array{module: string, permissions: array<int, mixed>}>
     */
    private function groupedPermissions(): array
    {
        return Permission::query()
            ->where('guard_name', config('auth.defaults.guard', 'web'))
            ->orderBy('module')
            ->orderBy('name')
            ->get()
            ->groupBy('module')
            ->map(fn ($permissions, $module): array => [
                'module' => (string) $module,
                'permissions' => PermissionResource::collection($permissions)->resolve(),
            ])
            ->values()
            ->all();
    }
}
