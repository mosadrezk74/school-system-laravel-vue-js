<?php

namespace Modules\Examination\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Examination\DTOs\CreateExamGradeDTO;
use Modules\Examination\Exceptions\ExamOperationException;
use Modules\Examination\Models\ExamGrade;
use Modules\Examination\Repositories\ExamGradeRepositoryInterface;

class CreateExamGradeAction extends BaseAction
{
    public function __construct(
        protected ExamGradeRepositoryInterface $repository,
    ) {}

    /**
     * @param  CreateExamGradeDTO  $input
     */
    public function execute(mixed $input): ExamGrade
    {
        /** @var CreateExamGradeDTO $dto */
        $dto = $input;

        return $this->executeWithTransaction(function () use ($dto): ExamGrade {
            if ($dto->minPercentage >= $dto->maxPercentage) {
                throw ExamOperationException::invalidPercentageRange();
            }

            if ($this->repository->existsByName($dto->gradeLevelId, $dto->subjectId, $dto->gradeName)) {
                throw ExamOperationException::gradeAlreadyExists();
            }

            return $this->repository->create($dto);
        });
    }
}
