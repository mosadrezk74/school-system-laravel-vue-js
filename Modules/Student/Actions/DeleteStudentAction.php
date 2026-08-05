<?php

namespace Modules\Student\Actions;

use Modules\Student\Exceptions\StudentOperationException;
use Modules\Student\Repositories\StudentRepository;
use Throwable;

class DeleteStudentAction
{
    public function __construct(
        protected StudentRepository $repository
    ) {}

    public function execute(int $id): bool
    {
        try {
            $user = $this->repository->findWithProfile($id);
            return $this->repository->delete($user);
        } catch (Throwable $e) {
            throw StudentOperationException::deleteFailed($e->getMessage());
        }
    }
}
