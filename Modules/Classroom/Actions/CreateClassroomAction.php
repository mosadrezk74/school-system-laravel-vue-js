<?php

namespace Modules\Classroom\Actions;

use Modules\Classroom\DTOs\CreateClassroomDTO;
use Modules\Classroom\Exceptions\ClassroomOperationException;
use Modules\Classroom\Interfaces\Repositories\ClassroomRepositoryInterface;
use Modules\Classroom\Models\Classroom;
use Modules\Core\Abstracts\BaseAction;

class CreateClassroomAction extends BaseAction
{
    public function __construct(
        protected ClassroomRepositoryInterface $repository,
    ) {}

    public function execute(mixed $input): Classroom
    {
        $dto = $input;

        return $this->executeWithTransaction(function () use ($dto): Classroom {
            if ($dto->code && $this->repository->existsByCode($dto->code)) {
                throw ClassroomOperationException::codeAlreadyExists();
            }

            return $this->repository->createFromDTO($dto, auth()->id());
        });
    }
}
