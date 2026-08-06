<?php

namespace Modules\Setting\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Core\Services\ModuleRegistry;

class ToggleModuleAction extends BaseAction
{
    public function __construct(
        private readonly ModuleRegistry $registry,
    ) {}

    /**
     * @param  array{name: string, enable: bool}  $input
     */
    public function execute(mixed $input): mixed
    {
        return $this->executeWithTransaction(function () use ($input): void {
            if ($input['enable']) {
                $this->registry->enable($input['name']);
            } else {
                $this->registry->disable($input['name']);
            }
        });
    }
}
