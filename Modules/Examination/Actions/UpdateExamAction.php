<?php

namespace Modules\Examination\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Core\Services\Translation\TranslationService;
use Modules\Examination\DTOs\UpdateExamDTO;
use Modules\Examination\Exceptions\ExamOperationException;
use Modules\Examination\Models\Exam;
use Modules\Examination\Repositories\ExamRepositoryInterface;

class UpdateExamAction extends BaseAction
{
    public function __construct(
        protected ExamRepositoryInterface $repository,
        protected TranslationService $translationService,
    ) {}

    /**
     * @param  array{exam: Exam, dto: UpdateExamDTO}  $input
     */
    public function execute(mixed $input): Exam
    {
        /** @var Exam $exam */
        $exam = $input['exam'];
        /** @var UpdateExamDTO $dto */
        $dto = $input['dto'];

        return $this->executeWithTransaction(function () use ($exam, $dto): Exam {
            if ($dto->code && $this->repository->existsByCode($dto->code, $exam->id)) {
                throw ExamOperationException::codeAlreadyExists();
            }

            $sourceLocale = $this->translationService->detectSourceLocale($dto->name);

            $dto = new UpdateExamDTO(
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

            return $this->repository->update($exam, $dto, auth()->id());
        });
    }
}
