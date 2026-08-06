<?php

namespace Modules\Examination\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Examination\DTOs\UpdateExamDTO;
use Modules\Examination\Exceptions\ExamOperationException;
use Modules\Examination\Models\Exam;
use Modules\Examination\Repositories\ExamRepositoryInterface;

class UpdateExamAction extends BaseAction
{
    public function __construct(
        protected ExamRepositoryInterface $repository,
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

            return $this->repository->update($exam, $dto, auth()->id());
        });
    }
}
