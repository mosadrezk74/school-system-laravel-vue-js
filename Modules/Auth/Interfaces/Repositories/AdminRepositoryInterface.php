<?php

namespace Modules\Auth\Interfaces\Repositories;

use App\Models\User;
use Modules\Auth\DTOs\CreateAdminDTO;
use Modules\Auth\DTOs\UpdateAdminDTO;

interface AdminRepositoryInterface
{
    public function existsByEmail(string $email, ?int $ignoreId = null): bool;

    public function create(CreateAdminDTO $dto, string $accountRole): User;

    public function update(User $admin, UpdateAdminDTO $dto): User;

    /**
     * @param  array<int, string>  $roles
     */
    public function syncRoles(User $admin, array $roles): User;

    public function delete(User $admin): bool;

    public function setActive(User $admin, bool $isActive): User;

    /**
     * Active accounts that can still administer the school, excluding the
     * given user — the number that must stay above zero.
     */
    public function otherActiveAdministratorsCount(?int $excludingUserId = null): int;
}
