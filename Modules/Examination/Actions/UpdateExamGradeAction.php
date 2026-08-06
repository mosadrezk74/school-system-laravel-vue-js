<?php

namespace Modules\Examination\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Examination\DTOs\UpdateExamGradeDTO;
use Modules\Examination\Exceptions\ExamOperationException;
use Modules\Examination\Models\ExamGrade;
use Modules\Examination\Repositories\ExamGradeRepositoryInterface;

class UpdateExamGradeAction extends BaseAction
{
    public function __construct(
        protected ExamGradeRepositoryInterface $repository,
    ) {}

    /**
     * @param  array{examGrade: ExamGrade, dto: UpdateExamGradeDTO}  $input
     */
    public function execute(mixed $input): ExamGrade
    {
        /** @var ExamGrade $examGrade */
        $examGrade = $input['examGrade'];
        /** @var UpdateExamGradeDTO $dto */
        $dto = $input['dto'];

        return $this->executeWithTransaction(function () use ($examGrade, $dto): ExamGrade {
            if ($dto->minPercentage >= $dto->maxPercentage) {
                throw ExamOperationException::invalidPercentageRange();
            }

            if ($this->repository->existsByName($dto->gradeLevelId, $dto->subjectId, $dto->gradeName, $examGrade->id)) {
                throw ExamOperationException::gradeAlreadyExists();
            }

            return $this->repository->update($examGrade, $dto);
        });
    }
}
