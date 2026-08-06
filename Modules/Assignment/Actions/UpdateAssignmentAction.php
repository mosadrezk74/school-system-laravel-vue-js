<?php

namespace Modules\Assignment\Actions;

use Modules\Assignment\DTOs\UpdateAssignmentDTO;
use Modules\Assignment\Models\Assignment;
use Modules\Assignment\Repositories\AssignmentRepositoryInterface;
use Modules\Core\Abstracts\BaseAction;
use Modules\Core\Services\Translation\TranslationService;

class UpdateAssignmentAction extends BaseAction
{
    public function __construct(
        protected AssignmentRepositoryInterface $repository,
        protected TranslationService $translationService,
    ) {}

    /**
     * @param  array{assignment: Assignment, dto: UpdateAssignmentDTO}  $input
     */
    public function execute(mixed $input): Assignment
    {
        /** @var Assignment $assignment */
        $assignment = $input['assignment'];
        /** @var UpdateAssignmentDTO $dto */
        $dto = $input['dto'];

        return $this->executeWithTransaction(function () use ($assignment, $dto): Assignment {
            $sourceLocale = $this->translationService->detectSourceLocale($dto->title);

            $dto = new UpdateAssignmentDTO(
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

            return $this->repository->update($assignment, $dto, auth()->id());
        });
    }
}
