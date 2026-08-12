<?php

namespace Modules\Auth\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Auth\Actions\CreateAdminAction;
use Modules\Auth\Actions\DeleteAdminAction;
use Modules\Auth\Actions\ToggleAdminActivationAction;
use Modules\Auth\Actions\UpdateAdminAction;
use Modules\Auth\DTOs\AdminFilterDTO;
use Modules\Auth\DTOs\CreateAdminDTO;
use Modules\Auth\DTOs\UpdateAdminDTO;
use Modules\Auth\Enums\UserRole;
use Modules\Auth\Http\Requests\StoreAdminRequest;
use Modules\Auth\Http\Requests\UpdateAdminRequest;
use Modules\Auth\Models\Role;
use Modules\Auth\Queries\AdminQuery;
use Modules\Auth\Transformers\AdminResource;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;

class AdminController extends BaseInertiaController
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', User::class);

        $filters = AdminFilterDTO::fromRequest($request);

        $admins = (new AdminQuery)
            ->withRoles()
            ->search($filters->search)
            ->isActive($filters->isActive)
            ->accountRole($filters->accountRole)
            ->sort($filters->sortBy, $filters->sortOrder)
            ->paginate($filters->perPage);

        return $this->render('Auth::Admins/Index', [
            'admins' => AdminResource::collection($admins),
            'filters' => $request->only([Pagination::SEARCH_PARAM, 'is_active', 'account_role']),
            'currentUserId' => $request->user()?->getKey(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', User::class);

        return $this->render('Auth::Admins/Create', [
            'roleOptions' => $this->roleOptions(),
            'accountRoleOptions' => $this->accountRoleOptions(),
        ]);
    }

    public function store(StoreAdminRequest $request, CreateAdminAction $action): RedirectResponse
    {
        $this->authorize('create', User::class);

        $action->execute(CreateAdminDTO::fromRequest($request));

        return $this->toRoute('auth.admins.index', [], __('auth::admins.created'));
    }

    public function edit(User $admin): Response
    {
        $this->authorize('update', $admin);

        return $this->render('Auth::Admins/Edit', [
            'admin' => new AdminResource($admin->load('roles')),
            'roleOptions' => $this->roleOptions(),
            'accountRoleOptions' => $this->accountRoleOptions(),
        ]);
    }

    public function update(UpdateAdminRequest $request, User $admin, UpdateAdminAction $action): RedirectResponse
    {
        $this->authorize('update', $admin);

        $action->execute([
            'admin' => $admin,
            'dto' => UpdateAdminDTO::fromRequest($request),
        ]);

        return $this->toRoute('auth.admins.index', [], __('auth::admins.updated'));
    }

    public function destroy(User $admin, DeleteAdminAction $action): RedirectResponse
    {
        $this->authorize('delete', $admin);

        $action->execute($admin);

        return $this->toRoute('auth.admins.index', [], __('auth::admins.deleted'));
    }

    /**
     * Activation is its own endpoint rather than a field on update, so a
     * suspension is one deliberate click and never a side effect of saving
     * an unrelated profile edit.
     */
    public function toggleActivation(
        Request $request,
        User $admin,
        ToggleAdminActivationAction $action,
    ): RedirectResponse {
        $this->authorize('update', $admin);

        $isActive = $request->boolean('is_active');

        $action->execute(['admin' => $admin, 'isActive' => $isActive]);

        return $this->backWithSuccess(
            $isActive ? __('auth::admins.activated') : __('auth::admins.deactivated'),
        );
    }

    /**
     * Spatie role names the actor may assign. The owner role is offered only
     * to an owner; AdminGuardService refuses it server-side regardless.
     *
     * @return array<int, array{value: string, title: string}>
     */
    private function roleOptions(): array
    {
        return Role::query()
            ->orderBy('name')
            ->get()
            ->reject(fn (Role $role): bool => $role->name === UserRole::Owner->value
                && auth()->user()?->role !== UserRole::Owner)
            ->map(fn (Role $role): array => [
                'value' => $role->name,
                'title' => $this->roleLabel($role->name),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{value: string, title: string}>
     */
    private function accountRoleOptions(): array
    {
        $roles = auth()->user()?->role === UserRole::Owner
            ? [UserRole::Owner, UserRole::Admin]
            : [UserRole::Admin];

        return array_map(fn (UserRole $role): array => [
            'value' => $role->value,
            'title' => $role->label(),
        ], $roles);
    }

    private function roleLabel(string $name): string
    {
        $key = "auth::roles.{$name}";
        $translated = __($key);

        return is_string($translated) && $translated !== $key ? $translated : $name;
    }
}
