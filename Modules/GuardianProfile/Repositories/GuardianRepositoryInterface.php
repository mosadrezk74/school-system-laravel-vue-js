<?php

namespace Modules\GuardianProfile\Repositories;

use Modules\GuardianProfile\DTOs\CreateGuardianDTO;
use Modules\GuardianProfile\DTOs\LinkGuardianStudentDTO;
use Modules\GuardianProfile\DTOs\UpdateGuardianDTO;
use Modules\GuardianProfile\Models\GuardianProfile;
use Modules\GuardianProfile\Models\GuardianStudent;

interface GuardianRepositoryInterface
{
    public function findById(int $id): ?GuardianProfile;

    public function findByIdOrFail(int $id): GuardianProfile;

    public function existsByEmail(string $email, ?int $excludeUserId = null): bool;

    public function existsByUsername(string $username, ?int $excludeUserId = null): bool;

    public function create(CreateGuardianDTO $dto, ?int $actorId = null): GuardianProfile;

    public function update(GuardianProfile $guardian, UpdateGuardianDTO $dto, ?int $actorId = null): GuardianProfile;

    public function delete(GuardianProfile $guardian): bool;

    public function restore(GuardianProfile $guardian): bool;

    public function isLinked(GuardianProfile $guardian, int $studentId): bool;

    public function linkStudent(GuardianProfile $guardian, LinkGuardianStudentDTO $dto): GuardianStudent;

    public function unlinkStudent(GuardianProfile $guardian, int $studentId): bool;
}
