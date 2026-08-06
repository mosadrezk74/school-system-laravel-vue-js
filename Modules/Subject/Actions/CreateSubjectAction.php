<?php

namespace Modules\Subject\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Subject\Exceptions\SubjectOperationException;
use Modules\Subject\Interfaces\Repositories\SubjectRepositoryInterface;
use Modules\Subject\Models\Subject;

class CreateSubjectAction extends BaseAction
{
    public function __construct(
        protected SubjectRepositoryInterface $repository,
    ) {}

    public function execute(mixed $input): Subject
    {
        $dto = $input;

        return $this->executeWithTransaction(function () use ($dto): Subject {
            if ($dto->code && $this->repository->existsByCode($dto->code)) {
                throw SubjectOperationException::codeAlreadyExists();
            }

            return $this->repository->createFromDTO($dto, auth()->id());
        });
    }
}
