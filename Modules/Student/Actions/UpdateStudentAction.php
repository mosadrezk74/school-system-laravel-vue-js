<?php

namespace Modules\Student\Actions;

use App\Models\User;
use Modules\Student\DTOs\StudentData;
use Modules\Student\Exceptions\StudentOperationException;
use Modules\Student\Repositories\StudentRepository;
use Throwable;

class UpdateStudentAction
{
    public function __construct(
        protected StudentRepository $repository
    ) {}

    public function execute(int $id, StudentData $data): User
    {
        try {
            $user = $this->repository->findWithProfile($id);
            return $this->repository->update($user, $data);
        } catch (Throwable $e) {
            throw StudentOperationException::updateFailed($e->getMessage());
        }
    }
}
