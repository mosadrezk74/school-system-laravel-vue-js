<?php

namespace Modules\Examination\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Examination\Models\ExamGrade;
use Modules\Examination\Repositories\ExamGradeRepositoryInterface;

class DeleteExamGradeAction extends BaseAction
{
    public function __construct(
        protected ExamGradeRepositoryInterface $repository,
    ) {}

    /**
     * @param  ExamGrade  $input
     */
    public function execute(mixed $input): bool
    {
        /** @var ExamGrade $examGrade */
        $examGrade = $input;

        return $this->executeWithTransaction(function () use ($examGrade): bool {
            return $this->repository->delete($examGrade);
        });
    }
}
