<?php

namespace Modules\Examination\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Examination\Models\ExamSubject;
use Modules\Examination\Repositories\ExamSubjectRepositoryInterface;

class RemoveExamSubjectAction extends BaseAction
{
    public function __construct(
        protected ExamSubjectRepositoryInterface $repository,
    ) {}

    /**
     * @param  ExamSubject  $input
     */
    public function execute(mixed $input): bool
    {
        /** @var ExamSubject $examSubject */
        $examSubject = $input;

        return $this->repository->delete($examSubject);
    }
}
