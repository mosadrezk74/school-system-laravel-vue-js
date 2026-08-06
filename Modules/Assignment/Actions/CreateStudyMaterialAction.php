<?php

namespace Modules\Assignment\Actions;

use Modules\Assignment\DTOs\CreateStudyMaterialDTO;
use Modules\Assignment\Models\StudyMaterial;
use Modules\Assignment\Repositories\StudyMaterialRepositoryInterface;
use Modules\Core\Abstracts\BaseAction;
use Modules\Core\Services\Translation\TranslationService;

class CreateStudyMaterialAction extends BaseAction
{
    public function __construct(
        protected StudyMaterialRepositoryInterface $repository,
        protected TranslationService $translationService,
    ) {}

    /**
     * @param  CreateStudyMaterialDTO  $input
     */
    public function execute(mixed $input): StudyMaterial
    {
        /** @var CreateStudyMaterialDTO $dto */
        $dto = $input;

        return $this->executeWithTransaction(function () use ($dto): StudyMaterial {
            $sourceLocale = $this->translationService->detectSourceLocale($dto->title);

            $dto = new CreateStudyMaterialDTO(
                classId: $dto->classId,
                subjectId: $dto->subjectId,
                teacherId: $dto->teacherId,
                academicYearId: $dto->academicYearId,
                semesterId: $dto->semesterId,
                title: $this->translationService->fillMissing($dto->title, $sourceLocale),
                description: $dto->description ? $this->translationService->fillMissing($dto->description, $sourceLocale) : null,
                materialType: $dto->materialType,
                isPublished: $dto->isPublished,
            );

            return $this->repository->create($dto, auth()->id());
        });
    }
}
