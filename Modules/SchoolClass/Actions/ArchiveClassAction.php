<?php

namespace Modules\SchoolClass\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\SchoolClass\Models\SchoolClass;
use Modules\SchoolClass\Repositories\ClassRepositoryInterface;

/**
 * Archives a class: soft-deletes it, without ever hard-deleting it.
 * Pair with RestoreClassAction.
 */
class ArchiveClassAction extends BaseAction
{
    public function __construct(
        protected ClassRepositoryInterface $repository,
    ) {}

    /**
     * @param  SchoolClass  $input
     */
    public function execute(mixed $input): bool
    {
        /** @var SchoolClass $class */
        $class = $input;

        return $this->executeWithTransaction(fn (): bool => $this->repository->delete($class));
    }
}
