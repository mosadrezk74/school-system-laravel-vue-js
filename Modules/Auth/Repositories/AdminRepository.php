<?php

namespace Modules\Auth\Repositories;

use App\Models\User;
use Modules\Auth\DTOs\CreateAdminDTO;
use Modules\Auth\DTOs\UpdateAdminDTO;
use Modules\Auth\Enums\UserRole;
use Modules\Auth\Interfaces\Repositories\AdminRepositoryInterface;

class AdminRepository implements AdminRepositoryInterface
{
    /**
     * Soft-deleted users still occupy their email in the unique index, so
     * the check has to see them too.
     */
    public function existsByEmail(string $email, ?int $ignoreId = null): bool
    {
        return User::withTrashed()
            ->where('email', $email)
            ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists();
    }

    public function create(CreateAdminDTO $dto, string $accountRole): User
    {
        return User::create([
            ...$dto->toUserArray(),
            'role' => $accountRole,
        ]);
    }

    public function update(User $admin, UpdateAdminDTO $dto): User
    {
        $admin->update($dto->toUserArray());

        return $admin->refresh();
    }

    /**
     * @param  array<int, string>  $roles
     */
    public function syncRoles(User $admin, array $roles): User
    {
        $admin->syncRoles($roles);

        return $admin->load('roles');
    }

    /** Soft delete: invoices, results and audit rows still reference this id. */
    public function delete(User $admin): bool
    {
        return (bool) $admin->delete();
    }

    public function setActive(User $admin, bool $isActive): User
    {
        $admin->update(['is_active' => $isActive]);

        return $admin->refresh();
    }

    public function otherActiveAdministratorsCount(?int $excludingUserId = null): int
    {
        return User::query()
            ->whereIn('role', [UserRole::Owner->value, UserRole::Admin->value])
            ->where('is_active', true)
            ->when($excludingUserId !== null, fn ($query) => $query->whereKeyNot($excludingUserId))
            ->count();
    }
}
