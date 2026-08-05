<?php

namespace Modules\Core\Interfaces;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

interface RepositoryInterface
{
    public function findById(int $id): ?Model;

    public function findOrFail(int $id): Model;

    public function findAll(array $filters = []): array;

    public function findAllPaginated(int $perPage = 15, array $filters = []);

    public function create(array $data): Model;

    public function update(int $id, array $data): ?Model;

    public function delete(int $id): bool;

    public function findByField(string $field, mixed $value): ?Model;

    public function findManyByField(string $field, mixed $value): array;

    public function query(): Builder;

    public function beginTransaction(): void;

    public function commit(): void;

    public function rollback(): void;

    public function withTrashed(int $id): ?Model;

    public function restore(int $id): bool;

    public function forceDelete(int $id): bool;
}
