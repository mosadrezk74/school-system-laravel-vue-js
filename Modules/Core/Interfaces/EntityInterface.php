<?php
namespace Modules\Core\Interfaces;

interface EntityInterface
{
    public function toArray(): array;
    public function toModel(): array;
}
