<?php

namespace Modules\Examination\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Examination\DTOs\CreateExamDTO;
use Modules\Examination\Models\Exam;
use Modules\Examination\Repositories\ExamRepositoryInterface;

class CreateExamAction extends BaseAction
{
    public function __construct(
        protected ExamRepositoryInterface $repository,
    ) {}

    /**
     * @param  CreateExamDTO  $input
     */
    public function execute(mixed $input): Exam
    {
        /** @var CreateExamDTO $dto */
        $dto = $input;

        return $this->executeWithTransaction(function () use ($dto): Exam {
            return $this->repository->create($dto, auth()->id());
        });
    }
}
