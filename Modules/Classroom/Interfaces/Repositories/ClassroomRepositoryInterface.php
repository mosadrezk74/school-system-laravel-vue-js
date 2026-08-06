<?php

namespace Modules\Classroom\Interfaces\Repositories;

use Modules\Classroom\DTOs\CreateClassroomDTO;
use Modules\Classroom\DTOs\UpdateClassroomDTO;
use Modules\Classroom\Models\Classroom;
use Modules\Core\Interfaces\RepositoryInterface;

interface ClassroomRepositoryInterface extends RepositoryInterface
{
    public function findById(int $id): ?Classroom;
    public function findOrFail(int $id): Classroom;
    public function findAll(array $filters = []): array;
    public function create(array $data): Classroom;
    public function update(int $id, array $data): ?Classroom;
    public function delete(int $id): bool;
    public function findByField(string $field, mixed $value): ?Classroom;
    public function findManyByField(string $field, mixed $value): array;
    public function withTrashed(int $id): ?Classroom;
    public function restore(int $id): bool;
    public function forceDelete(int $id): bool;
    public function findByCode(string $code): ?Classroom;
    public function existsByCode(string $code, ?int $excludeId = null): bool;
    public function createFromDTO(CreateClassroomDTO $dto, ?int $actorId = null): Classroom;
    public function updateFromDTO(int $id, UpdateClassroomDTO $dto, ?int $actorId = null): Classroom;
    public function getActiveClassrooms(): iterable;
}
