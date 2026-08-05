<?php
namespace Modules\Core\Abstracts;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Interfaces\EntityInterface;
use Modules\Core\Interfaces\MapperInterface;

abstract class BaseMapper implements MapperInterface
{
    abstract public function toEntity(Model $model): EntityInterface;
    abstract public function toModel(EntityInterface $entity): array;

    public function toEntityCollection(iterable $models): array
    {
        $items = is_array($models) ? $models : $models->all();

        return array_map(
            fn(Model $model) => $this->toEntity($model),
            $items
        );
    }
}
