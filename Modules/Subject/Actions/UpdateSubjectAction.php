<?php

namespace Modules\Subject\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Subject\DTOs\UpdateSubjectDTO;
use Modules\Subject\Exceptions\SubjectOperationException;
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

        return $this->executeWithTransaction(function () use ($subject, $dto): Subject {
            if ($dto->code && $this->repository->existsByCode($dto->code, $subject->id)) {
                throw SubjectOperationException::codeAlreadyExists();
            }

            return $this->repository->updateFromDTO($subject->id, $dto, auth()->id());
        });
    }
}
