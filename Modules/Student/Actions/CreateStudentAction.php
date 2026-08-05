<?php

namespace Modules\Student\Actions;

use App\Models\User;
use Modules\Student\DTOs\StudentData;
use Modules\Student\Exceptions\StudentOperationException;
use Modules\Student\Repositories\StudentRepository;
use Throwable;

class CreateStudentAction
{
    public function __construct(
        protected StudentRepository $repository
    ) {}

    public function execute(StudentData $data): User
    {
        try {
            return $this->repository->create($data);
        } catch (Throwable $e) {
            throw StudentOperationException::createFailed($e->getMessage());
        }
    }
}
