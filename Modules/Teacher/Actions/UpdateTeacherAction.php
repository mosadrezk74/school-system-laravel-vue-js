<?php

namespace Modules\Teacher\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Teacher\Exceptions\TeacherOperationException;
use Modules\Teacher\Interfaces\Repositories\TeacherRepositoryInterface;
use Modules\Teacher\Models\TeacherProfile;

class UpdateTeacherAction extends BaseAction
{
    public function __construct(
        protected TeacherRepositoryInterface $repository,
    ) {}

    public function execute(mixed $input): TeacherProfile
    {
        $teacher = $input['teacher'];
        $dto = $input['dto'];

        return $this->executeWithTransaction(function () use ($teacher, $dto): TeacherProfile {
            if ($this->repository->existsByEmail($dto->email, $teacher->user_id)) {
                throw TeacherOperationException::emailAlreadyExists();
            }

            if ($dto->employeeId && $this->repository->existsByEmployeeId($dto->employeeId, $teacher->id)) {
                throw TeacherOperationException::employeeIdAlreadyExists();
            }

            $teacher->user->update($dto->toUserArray());

            $updated = $this->repository->updateFromDTO($teacher->id, $dto, auth()->id());

            if ($dto->subjectIds !== null) {
                $updated->subjects()->sync($dto->subjectIds);
            }

            return $updated->load(['user', 'subjects']);
        });
    }
}
