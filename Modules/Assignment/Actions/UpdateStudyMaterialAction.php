<?php

namespace Modules\Assignment\Actions;

use Modules\Assignment\DTOs\UpdateStudyMaterialDTO;
use Modules\Assignment\Models\StudyMaterial;
use Modules\Assignment\Repositories\StudyMaterialRepositoryInterface;
use Modules\Core\Abstracts\BaseAction;
use Modules\Core\Services\Translation\TranslationService;

class UpdateStudyMaterialAction extends BaseAction
{
    public function __construct(
        protected StudyMaterialRepositoryInterface $repository,
        protected TranslationService $translationService,
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
            $sourceLocale = $this->translationService->detectSourceLocale($dto->title);

            $dto = new UpdateStudyMaterialDTO(
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

            return $this->repository->update($studyMaterial, $dto, auth()->id());
        });
    }
}
