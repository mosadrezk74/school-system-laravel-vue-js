<?php

namespace Modules\Academic\Actions;

use Modules\Academic\DTOs\UpdateGradeLevelDTO;
use Modules\Academic\Interfaces\Repositories\GradeLevelRepositoryInterface;
use Modules\Academic\Models\GradeLevel;
use Modules\Core\Abstracts\BaseAction;

class UpdateGradeLevelAction extends BaseAction
{
    public function __construct(
        protected GradeLevelRepositoryInterface $repository,
    ) {}

    /**
     * @param  array{gradeLevel: GradeLevel, dto: UpdateGradeLevelDTO}  $input
     */
    public function execute(mixed $input): GradeLevel
    {
        /** @var GradeLevel $gradeLevel */
        $gradeLevel = $input['gradeLevel'];
        /** @var UpdateGradeLevelDTO $dto */
        $dto = $input['dto'];

        return $this->executeWithTransaction(
            fn (): GradeLevel => $this->repository->updateFromDTO($gradeLevel, $dto),
        );
    }
}
