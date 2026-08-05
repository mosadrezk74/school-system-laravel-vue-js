<?php

namespace Modules\Classroom\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Modules\Classroom\DTOs\CreateClassroomDTO;
use Modules\Classroom\DTOs\UpdateClassroomDTO;
use Modules\Classroom\Interfaces\Repositories\ClassroomRepositoryInterface;
use Modules\Classroom\Models\Classroom;

class ClassroomRepository implements ClassroomRepositoryInterface
{
    public function findById(int $id): ?Classroom
    {
        return Classroom::query()->find($id);
    }

    public function findOrFail(int $id): Classroom
    {
        return Classroom::query()->findOrFail($id);
    }

    public function findAll(array $filters = []): array
    {
        $query = Classroom::query()->with(['branch']);
        
        foreach ($filters as $field => $value) {
            if ($value !== null) {
                $query->where($field, $value);
            }
        }
        
        return $query->get()->all();
    }

    public function findAllPaginated(int $perPage = 15, array $filters = [])
    {
        $query = Classroom::query()->with(['branch']);
        
        foreach ($filters as $field => $value) {
            if ($value !== null) {
                $query->where($field, $value);
            }
        }
        
        return $query->paginate($perPage);
    }

    public function create(array $data): Classroom
    {
        return Classroom::create($data);
    }

    public function update(int $id, array $data): ?Classroom
    {
        $classroom = $this->findById($id);
        
        if (!$classroom) {
            return null;
        }
        
        $classroom->update($data);
        
        return $classroom->refresh();
    }

    public function delete(int $id): bool
    {
        $classroom = $this->findById($id);
        
        if (!$classroom) {
            return false;
        }
        
        return (bool) $classroom->delete();
    }

    public function findByField(string $field, mixed $value): ?Classroom
    {
        return Classroom::query()->where($field, $value)->first();
    }

    public function findManyByField(string $field, mixed $value): array
    {
        return Classroom::query()->where($field, $value)->get()->all();
    }

    public function query(): Builder
    {
        return Classroom::query();
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

    public function withTrashed(int $id): ?Classroom
    {
        return Classroom::withTrashed()->find($id);
    }

    public function restore(int $id): bool
    {
        $classroom = $this->withTrashed($id);
        
        if (!$classroom) {
            return false;
        }
        
        return (bool) $classroom->restore();
    }

    public function forceDelete(int $id): bool
    {
        $classroom = $this->withTrashed($id);
        
        if (!$classroom) {
            return false;
        }
        
        return (bool) $classroom->forceDelete();
    }

    public function findByCode(string $code): ?Classroom
    {
        return Classroom::query()->where('code', $code)->first();
    }

    public function existsByCode(string $code, ?int $excludeId = null): bool
    {
        return Classroom::query()
            ->where('code', $code)
            ->when($excludeId, fn ($query) => $query->whereKeyNot($excludeId))
            ->exists();
    }

    public function createFromDTO(CreateClassroomDTO $dto, ?int $actorId = null): Classroom
    {
        $data = $dto->toArray();
        $data['created_by'] = $actorId;
        $data['updated_by'] = $actorId;

        return $this->create($data);
    }

    public function updateFromDTO(int $id, UpdateClassroomDTO $dto, ?int $actorId = null): Classroom
    {
        $data = $dto->toArray();
        $data['updated_by'] = $actorId;

        $classroom = $this->update($id, $data);
        
        if (!$classroom) {
            throw new \RuntimeException("Classroom with ID {$id} not found");
        }
        
        return $classroom;
    }

    public function getActiveClassrooms(): iterable
    {
        return Classroom::query()->where('is_active', true)->get();
    }

    public function getClassroomsByBranch(int $branchId): iterable
    {
        return Classroom::query()
            ->where('branch_id', $branchId)
            ->where('is_active', true)
            ->get();
    }
}
