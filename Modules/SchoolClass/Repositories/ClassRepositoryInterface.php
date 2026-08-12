<?php

namespace Modules\SchoolClass\Repositories;

use Modules\SchoolClass\DTOs\CreateClassDTO;
use Modules\SchoolClass\DTOs\UpdateClassDTO;
use Modules\SchoolClass\Models\SchoolClass;

interface ClassRepositoryInterface
{
    public function findById(int $id): ?SchoolClass;

    public function findByIdOrFail(int $id): SchoolClass;

    /**
     * Whether a section of this name already exists in the given year and
     * grade. Sections share a grade level, so the name is part of the key.
     */
    public function existsForGradeLevel(int $gradeLevelId, string $name, ?int $excludeId = null): bool;

    public function create(CreateClassDTO $dto, ?int $actorId = null): SchoolClass;

    public function update(SchoolClass $class, UpdateClassDTO $dto, ?int $actorId = null): SchoolClass;

    public function delete(SchoolClass $class): bool;

    public function restore(SchoolClass $class): bool;
}
