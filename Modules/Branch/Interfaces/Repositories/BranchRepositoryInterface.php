<?php

namespace Modules\Branch\Interfaces\Repositories;

use Modules\Branch\DTOs\CreateBranchDTO;
use Modules\Branch\DTOs\UpdateBranchDTO;
use Modules\Branch\Models\Branch;
use Modules\Core\Interfaces\RepositoryInterface;

interface BranchRepositoryInterface extends RepositoryInterface
{
    public function findById(int $id): ?Branch;
    public function findOrFail(int $id): Branch;
    public function findAll(array $filters = []): array;
    public function create(array $data): Branch;
    public function update(int $id, array $data): ?Branch;
    public function delete(int $id): bool;
    public function findByField(string $field, mixed $value): ?Branch;
    public function findManyByField(string $field, mixed $value): array;
    public function withTrashed(int $id): ?Branch;
    public function restore(int $id): bool;
    public function forceDelete(int $id): bool;
    
    public function findByCode(string $code): ?Branch;
    public function existsByCode(string $code, ?int $excludeId = null): bool;
    public function createFromDTO(CreateBranchDTO $dto, ?int $actorId = null): Branch;
    public function updateFromDTO(int $id, UpdateBranchDTO $dto, ?int $actorId = null): Branch;
    public function getMainBranch(): ?Branch;
    public function getActiveBranches(): iterable;
}