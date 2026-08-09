<?php

namespace Modules\Academic\Actions;

use Modules\Academic\Interfaces\Repositories\GradeLevelRepositoryInterface;
use Modules\Academic\Models\GradeLevel;
use Modules\Core\Abstracts\BaseAction;

class ArchiveGradeLevelAction extends BaseAction
{
    public function __construct(
        protected GradeLevelRepositoryInterface $repository,
    ) {}

    /**
     * @param  GradeLevel  $input
     */
    public function execute(mixed $input): bool
    {
        /** @var GradeLevel $gradeLevel */
        $gradeLevel = $input;

        return $this->executeWithTransaction(fn (): bool => $this->repository->delete($gradeLevel));
    }
}
