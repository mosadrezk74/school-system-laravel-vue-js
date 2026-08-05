<?php
namespace Modules\Core\Abstracts;

use Modules\Core\Interfaces\EntityInterface;

abstract class BaseEntity implements EntityInterface
{
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    public function toModel(): array
    {
        return $this->toArray();
    }
}
