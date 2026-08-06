<?php

namespace Modules\Examination\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Core\Services\Translation\TranslationService;
use Modules\Examination\DTOs\CreateExamDTO;
use Modules\Examination\Exceptions\ExamOperationException;
use Modules\Examination\Models\Exam;
use Modules\Examination\Repositories\ExamRepositoryInterface;

class CreateExamAction extends BaseAction
{
    public function __construct(
        protected ExamRepositoryInterface $repository,
        protected TranslationService $translationService,
    ) {}

    /**
     * @param  CreateExamDTO  $input
     */
    public function execute(mixed $input): Exam
    {
        /** @var CreateExamDTO $dto */
        $dto = $input;

        return $this->executeWithTransaction(function () use ($dto): Exam {
            if ($dto->code && $this->repository->existsByCode($dto->code)) {
                throw ExamOperationException::codeAlreadyExists();
            }

            $sourceLocale = $this->translationService->detectSourceLocale($dto->name);

            $dto = new CreateExamDTO(
                academicYearId: $dto->academicYearId,
                semesterId: $dto->semesterId,
                classId: $dto->classId,
                name: $this->translationService->fillMissing($dto->name, $sourceLocale),
                code: $dto->code,
                examType: $dto->examType,
                startDate: $dto->startDate,
                endDate: $dto->endDate,
                passingGrade: $dto->passingGrade,
                totalMarks: $dto->totalMarks,
                weightage: $dto->weightage,
                isPublished: $dto->isPublished,
            );

            return $this->repository->create($dto, auth()->id());
        });
    }
}
