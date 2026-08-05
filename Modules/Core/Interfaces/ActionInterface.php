<?php

namespace Modules\Core\Interfaces;

interface ActionInterface
{
    public function execute(mixed $input): mixed;
}
