<?php

namespace Modules\Core\Abstracts;

use Modules\Core\Interfaces\ServiceInterface;
use Modules\Core\Interfaces\RepositoryInterface;
use Modules\Core\Traits\CacheableTrait;
use Modules\Core\Traits\ValidationTrait;

abstract class BaseService implements ServiceInterface
{
    use CacheableTrait, ValidationTrait;

    protected RepositoryInterface $repository;
    protected array $validationRules = [];

    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function findById(int $id)
    {
        return $this->repository->findById($id);
    }

    public function findOrFail(int $id)
    {
        return $this->repository->findOrFail($id);
    }

    public function findAll(array $filters = []): array
    {
        return $this->repository->findAll($filters);
    }

    public function findAllPaginated(int $perPage = 15, array $filters = [])
    {
        return $this->repository->findAllPaginated($perPage, $filters);
    }

    public function create(array $data)
    {
        $this->validate($data);
        $this->beforeCreate($data);
        $result = $this->repository->create($data);
        $this->afterCreate($result);
        return $result;
    }

    public function update(int $id, array $data)
    {
        $this->validate($data, true);
        $this->beforeUpdate($id, $data);
        $result = $this->repository->update($id, $data);
        $this->afterUpdate($result);
        return $result;
    }

    public function delete(int $id): bool
    {
        $this->beforeDelete($id);
        $result = $this->repository->delete($id);
        $this->afterDelete($id);
        return $result;
    }

    protected function beforeCreate(array &$data): void {}
    protected function afterCreate($model): void {}
    protected function beforeUpdate(int $id, array &$data): void {}
    protected function afterUpdate($model): void {}
    protected function beforeDelete(int $id): void {}
    protected function afterDelete(int $id): void {}

    protected function validate(array $data, bool $isUpdate = false): void
    {
        if (empty($this->validationRules)) {
            return;
        }

        $rules = $isUpdate ? $this->getUpdateRules() : $this->validationRules;
        $this->validateData($data, $rules);
    }

    protected function getUpdateRules(): array
    {
        $rules = [];
        foreach ($this->validationRules as $field => $rule) {
            if (str_contains($rule, 'required')) {
                $rules[$field] = str_replace('required', 'sometimes', $rule);
            } else {
                $rules[$field] = $rule;
            }
        }
        return $rules;
    }

    protected function getCachePrefix(): string
    {
        return class_basename($this);
    }
}
