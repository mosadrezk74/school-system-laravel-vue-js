<?php

namespace Modules\Subject\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Subject\Interfaces\Repositories\SubjectRepositoryInterface;
use Modules\Subject\Models\Subject;

class ArchiveSubjectAction extends BaseAction
{
    public function __construct(
        protected SubjectRepositoryInterface $repository,
    ) {}

    public function execute(mixed $input): bool
    {
        $subject = $input;

        return $this->executeWithTransaction(function () use ($subject): bool {
            return $this->repository->delete($subject->id);
        });
    }
}
