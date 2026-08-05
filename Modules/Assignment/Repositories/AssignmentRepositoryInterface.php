<?php

namespace Modules\Assignment\Repositories;

use Modules\Assignment\DTOs\CreateAssignmentDTO;
use Modules\Assignment\DTOs\UpdateAssignmentDTO;
use Modules\Assignment\Models\Assignment;

interface AssignmentRepositoryInterface
{
    public function findById(int $id): ?Assignment;

    public function findByIdOrFail(int $id): Assignment;

    public function create(CreateAssignmentDTO $dto, ?int $actorId = null): Assignment;

    public function update(Assignment $assignment, UpdateAssignmentDTO $dto, ?int $actorId = null): Assignment;

    public function delete(Assignment $assignment): bool;

    public function restore(Assignment $assignment): bool;
}
