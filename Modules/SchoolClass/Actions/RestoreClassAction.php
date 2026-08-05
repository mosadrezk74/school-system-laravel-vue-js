<?php

namespace Modules\SchoolClass\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\SchoolClass\Models\SchoolClass;
use Modules\SchoolClass\Repositories\ClassRepositoryInterface;

/**
 * Reverses ArchiveClassAction: restores the soft-deleted class.
 */
class RestoreClassAction extends BaseAction
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

        return $this->executeWithTransaction(fn (): bool => $this->repository->restore($class));
    }
}
