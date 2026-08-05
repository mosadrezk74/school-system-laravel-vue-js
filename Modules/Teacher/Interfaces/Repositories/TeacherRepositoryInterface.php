<?php

namespace Modules\Teacher\Interfaces\Repositories;

use Modules\Core\Interfaces\RepositoryInterface;
use Modules\Teacher\DTOs\CreateTeacherDTO;
use Modules\Teacher\DTOs\UpdateTeacherDTO;
use Modules\Teacher\Models\TeacherProfile;

interface TeacherRepositoryInterface extends RepositoryInterface
{
    public function findById(int $id): ?TeacherProfile;

    public function findOrFail(int $id): TeacherProfile;

    public function findAll(array $filters = []): array;

    public function create(array $data): TeacherProfile;

    public function update(int $id, array $data): ?TeacherProfile;

    public function delete(int $id): bool;

    public function findByField(string $field, mixed $value): ?TeacherProfile;

    public function findManyByField(string $field, mixed $value): array;

    public function withTrashed(int $id): ?TeacherProfile;

    public function restore(int $id): bool;

    public function forceDelete(int $id): bool;

    public function findByEmployeeId(string $employeeId): ?TeacherProfile;

    public function existsByEmployeeId(string $employeeId, ?int $excludeId = null): bool;

    public function createFromDTO(CreateTeacherDTO $dto, ?int $actorId = null): TeacherProfile;

    public function updateFromDTO(int $id, UpdateTeacherDTO $dto, ?int $actorId = null): TeacherProfile;

    public function getActiveTeachers(): iterable;

    public function getTeachersByDepartment(int $departmentId): iterable;
}
