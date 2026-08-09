<?php

namespace Modules\Academic\Actions;

use Modules\Academic\DTOs\CreateGradeLevelDTO;
use Modules\Academic\Interfaces\Repositories\GradeLevelRepositoryInterface;
use Modules\Academic\Models\GradeLevel;
use Modules\Core\Abstracts\BaseAction;

class CreateGradeLevelAction extends BaseAction
{
    public function __construct(
        protected GradeLevelRepositoryInterface $repository,
    ) {}

    /**
     * @param  CreateGradeLevelDTO  $input
     */
    public function execute(mixed $input): GradeLevel
    {
        /** @var CreateGradeLevelDTO $dto */
        $dto = $input;

        return $this->executeWithTransaction(fn (): GradeLevel => $this->repository->createFromDTO($dto));
    }
}
