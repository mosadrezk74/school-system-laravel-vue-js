<?php

namespace Modules\Examination\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Examination\Models\Exam;
use Modules\Examination\Repositories\ExamRepositoryInterface;

class RestoreExamAction extends BaseAction
{
    public function __construct(
        protected ExamRepositoryInterface $repository,
    ) {}

    /**
     * @param  Exam  $input
     */
    public function execute(mixed $input): bool
    {
        /** @var Exam $exam */
        $exam = $input;

        return $this->executeWithTransaction(function () use ($exam): bool {
            return $this->repository->restore($exam);
        });
    }
}
