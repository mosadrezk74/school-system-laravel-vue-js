<?php

namespace Modules\Assignment\Actions;

use Modules\Assignment\DTOs\UpdateStudyMaterialDTO;
use Modules\Assignment\Models\StudyMaterial;
use Modules\Assignment\Repositories\StudyMaterialRepositoryInterface;
use Modules\Core\Abstracts\BaseAction;

class UpdateStudyMaterialAction extends BaseAction
{
    public function __construct(
        protected StudyMaterialRepositoryInterface $repository,
    ) {}

    /**
     * @param  array{studyMaterial: StudyMaterial, dto: UpdateStudyMaterialDTO}  $input
     */
    public function execute(mixed $input): StudyMaterial
    {
        /** @var StudyMaterial $studyMaterial */
        $studyMaterial = $input['studyMaterial'];
        /** @var UpdateStudyMaterialDTO $dto */
        $dto = $input['dto'];

        return $this->executeWithTransaction(function () use ($studyMaterial, $dto): StudyMaterial {
            return $this->repository->update($studyMaterial, $dto, auth()->id());
        });
    }
}
