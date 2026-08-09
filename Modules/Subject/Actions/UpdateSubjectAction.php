<?php

namespace Modules\Subject\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Subject\Interfaces\Repositories\SubjectRepositoryInterface;
use Modules\Subject\Models\Subject;

class UpdateSubjectAction extends BaseAction
{
    public function __construct(
        protected SubjectRepositoryInterface $repository,
    ) {}

    public function execute(mixed $input): Subject
    {
        $subject = $input['subject'];
        $dto = $input['dto'];

        return $this->executeWithTransaction(
            fn (): Subject => $this->repository->updateFromDTO($subject->id, $dto, auth()->id()),
        );
    }
}
