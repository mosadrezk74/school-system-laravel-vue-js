<?php

namespace Modules\Branch\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Modules\Branch\DTOs\CreateBranchDTO;
use Modules\Branch\DTOs\UpdateBranchDTO;
use Modules\Branch\Interfaces\Repositories\BranchRepositoryInterface;
use Modules\Branch\Models\Branch;

class BranchRepository implements BranchRepositoryInterface
{

    public function findById(int $id): ?Branch
    {
        return Branch::query()->find($id);
    }

    public function findOrFail(int $id): Branch
    {
        return Branch::query()->findOrFail($id);
    }

    public function findAll(array $filters = []): array
    {
        $query = Branch::query();
        
        foreach ($filters as $field => $value) {
            if ($value !== null) {
                $query->where($field, $value);
            }
        }
        
        return $query->get()->all();
    }

    public function findAllPaginated(int $perPage = 15, array $filters = [])
    {
        $query = Branch::query();
        
        foreach ($filters as $field => $value) {
            if ($value !== null) {
                $query->where($field, $value);
            }
        }
        
        return $query->paginate($perPage);
    }

    public function create(array $data): Branch
    {
        return Branch::create($data);
    }

    public function update(int $id, array $data): ?Branch
    {
        $branch = $this->findById($id);
        
        if (!$branch) {
            return null;
        }
        
        $branch->update($data);
        
        return $branch->refresh();
    }

    public function delete(int $id): bool
    {
        $branch = $this->findById($id);
        
        if (!$branch) {
            return false;
        }
        
        return (bool) $branch->delete();
    }

    public function findByField(string $field, mixed $value): ?Branch
    {
        return Branch::query()->where($field, $value)->first();
    }

    public function findManyByField(string $field, mixed $value): array
    {
        return Branch::query()->where($field, $value)->get()->all();
    }

    public function query(): Builder
    {
        return Branch::query();
    }

    public function beginTransaction(): void
    {
        \DB::beginTransaction();
    }

    public function commit(): void
    {
        \DB::commit();
    }

    public function rollback(): void
    {
        \DB::rollBack();
    }

    public function withTrashed(int $id): ?Branch
    {
        return Branch::withTrashed()->find($id);
    }

    public function restore(int $id): bool
    {
        $branch = $this->withTrashed($id);
        
        if (!$branch) {
            return false;
        }
        
        return (bool) $branch->restore();
    }

    public function forceDelete(int $id): bool
    {
        $branch = $this->withTrashed($id);
        
        if (!$branch) {
            return false;
        }
        
        return (bool) $branch->forceDelete();
    }


    public function findByCode(string $code): ?Branch
    {
        return Branch::query()->where('code', $code)->first();
    }

    public function existsByCode(string $code, ?int $excludeId = null): bool
    {
        return Branch::query()
            ->where('code', $code)
            ->when($excludeId, fn ($query) => $query->whereKeyNot($excludeId))
            ->exists();
    }

    public function createFromDTO(CreateBranchDTO $dto, ?int $actorId = null): Branch
    {
        $data = $dto->toArray();
        $data['created_by'] = $actorId;
        $data['updated_by'] = $actorId;

        return $this->create($data);
    }

    public function updateFromDTO(int $id, UpdateBranchDTO $dto, ?int $actorId = null): Branch
    {
        $data = $dto->toArray();
        $data['updated_by'] = $actorId;

        $branch = $this->update($id, $data);
        
        if (!$branch) {
            throw new \RuntimeException("Branch with ID {$id} not found");
        }
        
        return $branch;
    }


    public function getMainBranch(): ?Branch
    {
        return Branch::query()->where('is_main', true)->first();
    }

    public function getActiveBranches(): iterable
    {
        return Branch::query()->where('is_active', true)->get();
    }
}