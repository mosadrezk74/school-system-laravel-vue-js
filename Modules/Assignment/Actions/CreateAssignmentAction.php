<?php

namespace Modules\Assignment\Actions;

use Modules\Assignment\DTOs\CreateAssignmentDTO;
use Modules\Assignment\Models\Assignment;
use Modules\Assignment\Repositories\AssignmentRepositoryInterface;
use Modules\Core\Abstracts\BaseAction;
use Modules\Core\Services\Translation\TranslationService;

class CreateAssignmentAction extends BaseAction
{
    public function __construct(
        protected AssignmentRepositoryInterface $repository,
        protected TranslationService $translationService,
    ) {}

    /**
     * @param  CreateAssignmentDTO  $input
     */
    public function execute(mixed $input): Assignment
    {
        /** @var CreateAssignmentDTO $dto */
        $dto = $input;

        return $this->executeWithTransaction(function () use ($dto): Assignment {
            $sourceLocale = $this->translationService->detectSourceLocale($dto->title);

            $dto = new CreateAssignmentDTO(
                classId: $dto->classId,
                subjectId: $dto->subjectId,
                teacherId: $dto->teacherId,
                academicYearId: $dto->academicYearId,
                semesterId: $dto->semesterId,
                title: $this->translationService->fillMissing($dto->title, $sourceLocale),
                description: $dto->description ? $this->translationService->fillMissing($dto->description, $sourceLocale) : null,
                maxScore: $dto->maxScore,
                deadline: $dto->deadline,
                assignmentType: $dto->assignmentType,
                isGroupWork: $dto->isGroupWork,
                isPublished: $dto->isPublished,
            );

            return $this->repository->create($dto, auth()->id());
        });
    }
}
