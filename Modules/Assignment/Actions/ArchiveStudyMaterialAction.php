<?php

namespace Modules\Assignment\Actions;

use Modules\Assignment\Models\StudyMaterial;
use Modules\Assignment\Repositories\StudyMaterialRepositoryInterface;
use Modules\Core\Abstracts\BaseAction;

class ArchiveStudyMaterialAction extends BaseAction
{
    public function __construct(
        protected StudyMaterialRepositoryInterface $repository,
    ) {}

    /**
     * @param  StudyMaterial  $input
     */
    public function execute(mixed $input): bool
    {
        /** @var StudyMaterial $studyMaterial */
        $studyMaterial = $input;

        return $this->executeWithTransaction(function () use ($studyMaterial): bool {
            return $this->repository->delete($studyMaterial);
        });
    }
}
