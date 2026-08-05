<?php

namespace Modules\GuardianProfile\Repositories;

use App\Models\User;
use Modules\Auth\Enums\UserRole;
use Modules\GuardianProfile\DTOs\CreateGuardianDTO;
use Modules\GuardianProfile\DTOs\UpdateGuardianDTO;
use Modules\GuardianProfile\Models\GuardianProfile;

/**
 * Data access only: no validation, no business rules, no transactions.
 * Callers (Actions) own the transaction boundary and business checks.
 */
class GuardianRepository implements GuardianRepositoryInterface
{
    public function findById(int $id): ?GuardianProfile
    {
        return GuardianProfile::query()->find($id);
    }

    public function findByIdOrFail(int $id): GuardianProfile
    {
        return GuardianProfile::query()->findOrFail($id);
    }

    public function existsByEmail(string $email, ?int $excludeUserId = null): bool
    {
        return User::query()
            ->where('email', $email)
            ->when($excludeUserId, fn ($query) => $query->whereKeyNot($excludeUserId))
            ->exists();
    }

    public function existsByUsername(string $username, ?int $excludeUserId = null): bool
    {
        return User::query()
            ->where('username', $username)
            ->when($excludeUserId, fn ($query) => $query->whereKeyNot($excludeUserId))
            ->exists();
    }

    public function create(CreateGuardianDTO $dto, ?int $actorId = null): GuardianProfile
    {
        $user = User::create([
            ...$dto->toUserArray(),
            'role' => UserRole::Guardian->value,
        ]);

        return $user->guardianProfile()->create([
            ...$dto->toProfileArray(),
            'created_by' => $actorId,
            'updated_by' => $actorId,
        ]);
    }

    public function update(GuardianProfile $guardian, UpdateGuardianDTO $dto, ?int $actorId = null): GuardianProfile
    {
        $guardian->user->update($dto->toUserArray());

        $guardian->update([
            ...$dto->toProfileArray(),
            'updated_by' => $actorId,
        ]);

        return $guardian->refresh();
    }

    public function delete(GuardianProfile $guardian): bool
    {
        return (bool) $guardian->delete();
    }

    public function restore(GuardianProfile $guardian): bool
    {
        return (bool) $guardian->restore();
    }
}
