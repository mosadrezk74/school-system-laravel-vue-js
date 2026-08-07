<?php

namespace Modules\StudyMaterial\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\StudyMaterial\Models\StudyResource;
use Modules\StudyMaterial\Repositories\StudyResourceRepositoryInterface;

class ArchiveStudyResourceAction extends BaseAction
{
    public function __construct(
        protected StudyResourceRepositoryInterface $repository,
    ) {}

    /**
     * @param  StudyResource  $input
     */
    public function execute(mixed $input): bool
    {
        /** @var StudyResource $studyResource */
        $studyResource = $input;

        return $this->executeWithTransaction(fn (): bool => $this->repository->delete($studyResource));
    }
}
