<?php

namespace Modules\Subject\Interfaces\Repositories;

use Modules\Core\Interfaces\RepositoryInterface;
use Modules\Subject\DTOs\CreateSubjectDTO;
use Modules\Subject\DTOs\UpdateSubjectDTO;
use Modules\Subject\Models\Subject;

interface SubjectRepositoryInterface extends RepositoryInterface
{
    public function findById(int $id): ?Subject;
    public function findOrFail(int $id): Subject;
    public function findAll(array $filters = []): array;
    public function create(array $data): Subject;
    public function update(int $id, array $data): ?Subject;
    public function delete(int $id): bool;
    public function findByField(string $field, mixed $value): ?Subject;
    public function findManyByField(string $field, mixed $value): array;
    public function withTrashed(int $id): ?Subject;
    public function restore(int $id): bool;
    public function forceDelete(int $id): bool;
    public function findByCode(string $code): ?Subject;
    public function existsByCode(string $code, ?int $excludeId = null): bool;
    public function createFromDTO(CreateSubjectDTO $dto, ?int $actorId = null): Subject;
    public function updateFromDTO(int $id, UpdateSubjectDTO $dto, ?int $actorId = null): Subject;
    public function getActiveSubjects(): iterable;
}
