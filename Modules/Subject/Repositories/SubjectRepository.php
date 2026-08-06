<?php

namespace Modules\Subject\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Modules\Subject\DTOs\CreateSubjectDTO;
use Modules\Subject\DTOs\UpdateSubjectDTO;
use Modules\Subject\Interfaces\Repositories\SubjectRepositoryInterface;
use Modules\Subject\Models\Subject;

class SubjectRepository implements SubjectRepositoryInterface
{
    public function findById(int $id): ?Subject
    {
        return Subject::query()->find($id);
    }

    public function findOrFail(int $id): Subject
    {
        return Subject::query()->findOrFail($id);
    }

    public function findAll(array $filters = []): array
    {
        $query = Subject::query();
        
        foreach ($filters as $field => $value) {
            if ($value !== null) {
                $query->where($field, $value);
            }
        }
        
        return $query->get()->all();
    }

    public function findAllPaginated(int $perPage = 15, array $filters = [])
    {
        $query = Subject::query();
        
        foreach ($filters as $field => $value) {
            if ($value !== null) {
                $query->where($field, $value);
            }
        }
        
        return $query->paginate($perPage);
    }

    public function create(array $data): Subject
    {
        return Subject::create($data);
    }

    public function update(int $id, array $data): ?Subject
    {
        $subject = $this->findById($id);
        
        if (!$subject) {
            return null;
        }
        
        $subject->update($data);
        
        return $subject->refresh();
    }

    public function delete(int $id): bool
    {
        $subject = $this->findById($id);
        
        if (!$subject) {
            return false;
        }
        
        return (bool) $subject->delete();
    }

    public function findByField(string $field, mixed $value): ?Subject
    {
        return Subject::query()->where($field, $value)->first();
    }

    public function findManyByField(string $field, mixed $value): array
    {
        return Subject::query()->where($field, $value)->get()->all();
    }

    public function query(): Builder
    {
        return Subject::query();
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

    public function withTrashed(int $id): ?Subject
    {
        return Subject::withTrashed()->find($id);
    }

    public function restore(int $id): bool
    {
        $subject = $this->withTrashed($id);
        
        if (!$subject) {
            return false;
        }
        
        return (bool) $subject->restore();
    }

    public function forceDelete(int $id): bool
    {
        $subject = $this->withTrashed($id);
        
        if (!$subject) {
            return false;
        }
        
        return (bool) $subject->forceDelete();
    }

    public function findByCode(string $code): ?Subject
    {
        return Subject::query()->where('code', $code)->first();
    }

    public function existsByCode(string $code, ?int $excludeId = null): bool
    {
        return Subject::query()
            ->where('code', $code)
            ->when($excludeId, fn ($query) => $query->whereKeyNot($excludeId))
            ->exists();
    }

    public function createFromDTO(CreateSubjectDTO $dto, ?int $actorId = null): Subject
    {
        $data = $dto->toArray();
        $data['created_by'] = $actorId;
        $data['updated_by'] = $actorId;

        return $this->create($data);
    }

    public function updateFromDTO(int $id, UpdateSubjectDTO $dto, ?int $actorId = null): Subject
    {
        $data = $dto->toArray();
        $data['updated_by'] = $actorId;

        $subject = $this->update($id, $data);
        
        if (!$subject) {
            throw new \RuntimeException("Subject with ID {$id} not found");
        }
        
        return $subject;
    }

    public function getActiveSubjects(): iterable
    {
        return Subject::query()->where('is_active', true)->get();
    }

}
