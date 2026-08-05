<?php

namespace Modules\Core\Abstracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Interfaces\RepositoryInterface;
use Modules\Core\Traits\CacheableTrait;

abstract class BaseRepository implements RepositoryInterface
{
    use CacheableTrait;

    protected Model $model;

    protected array $searchableFields = [];

    protected array $filterableFields = [];

    protected array $sortableFields = ['created_at'];

    protected int $cacheTtl = 3600;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function findById(int $id): ?Model
    {
        $cacheKey = $this->getCacheKey("find_{$id}");

        return $this->cacheRemember($cacheKey, function () use ($id) {
            return $this->model->find($id);
        }, $this->cacheTtl);
    }

    public function findOrFail(int $id): Model
    {
        return $this->model->findOrFail($id);
    }

    public function findAll(array $filters = []): array
    {
        $cacheKey = $this->getCacheKey('all_'.md5(json_encode($filters)));

        return $this->cacheRemember($cacheKey, function () use ($filters) {
            return $this->applyFilters($this->model->newQuery(), $filters)->get()->toArray();
        }, $this->cacheTtl);
    }

    public function findAllPaginated(int $perPage = 15, array $filters = [])
    {
        return $this->applyFilters($this->model->newQuery(), $filters)->paginate($perPage);
    }

    public function create(array $data): Model
    {
        $model = $this->model->create($data);
        $this->cacheFlush();

        return $model;
    }

    public function update(int $id, array $data): ?Model
    {
        $model = $this->findById($id);
        if ($model) {
            $model->update($data);
            $this->cacheFlush();

            return $model->fresh();
        }

        return null;
    }

    public function delete(int $id): bool
    {
        $model = $this->findById($id);
        if ($model) {
            $result = $model->delete();
            $this->cacheFlush();

            return $result;
        }

        return false;
    }

    public function findByField(string $field, mixed $value): ?Model
    {
        $cacheKey = $this->getCacheKey("field_{$field}_{$value}");

        return $this->cacheRemember($cacheKey, function () use ($field, $value) {
            return $this->model->where($field, $value)->first();
        }, $this->cacheTtl);
    }

    public function findManyByField(string $field, mixed $value): array
    {
        $cacheKey = $this->getCacheKey("many_{$field}_{$value}");

        return $this->cacheRemember($cacheKey, function () use ($field, $value) {
            return $this->model->where($field, $value)->get()->toArray();
        }, $this->cacheTtl);
    }

    public function query(): Builder
    {
        return $this->model->newQuery();
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

    public function withTrashed(int $id): ?Model
    {
        return $this->model->withTrashed()->find($id);
    }

    public function restore(int $id): bool
    {
        $model = $this->withTrashed($id);
        if ($model) {
            $result = $model->restore();
            $this->cacheFlush();

            return $result;
        }

        return false;
    }

    public function forceDelete(int $id): bool
    {
        $model = $this->withTrashed($id);
        if ($model) {
            $result = $model->forceDelete();
            $this->cacheFlush();

            return $result;
        }

        return false;
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        foreach ($filters as $field => $value) {
            if (in_array($field, $this->searchableFields)) {
                $query->where($field, 'LIKE', "%{$value}%");
            } elseif (in_array($field, $this->filterableFields)) {
                $query->where($field, $value);
            } elseif (in_array($field, $this->sortableFields)) {
                $direction = $filters['sort_direction'] ?? 'asc';
                $query->orderBy($field, $direction);
            }
        }

        return $query;
    }

    protected function getCachePrefix(): string
    {
        return class_basename($this->model);
    }
}
