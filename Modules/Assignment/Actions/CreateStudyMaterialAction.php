<?php

namespace Modules\Assignment\Actions;

use Modules\Assignment\DTOs\CreateStudyMaterialDTO;
use Modules\Assignment\Models\StudyMaterial;
use Modules\Assignment\Repositories\StudyMaterialRepositoryInterface;
use Modules\Core\Abstracts\BaseAction;

class CreateStudyMaterialAction extends BaseAction
{
    public function __construct(
        protected StudyMaterialRepositoryInterface $repository,
    ) {}

    /**
     * @param  CreateStudyMaterialDTO  $input
     */
    public function execute(mixed $input): StudyMaterial
    {
        /** @var CreateStudyMaterialDTO $dto */
        $dto = $input;

        return $this->executeWithTransaction(function () use ($dto): StudyMaterial {
            return $this->repository->create($dto, auth()->id());
        });
    }
}
