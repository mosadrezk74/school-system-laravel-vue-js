<?php

namespace Modules\Core\Interfaces;

use Illuminate\Database\Eloquent\Model;

interface MapperInterface
{
    public function toEntity(Model $model): EntityInterface;

    public function toModel(EntityInterface $entity): array;

    public function toEntityCollection(iterable $models): array;
}
