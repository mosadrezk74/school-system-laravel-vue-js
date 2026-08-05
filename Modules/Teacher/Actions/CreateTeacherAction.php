<?php

namespace Modules\Teacher\Actions;

use App\Models\User;
use Modules\Auth\Enums\UserRole;
use Modules\Core\Abstracts\BaseAction;
use Modules\Teacher\Exceptions\TeacherOperationException;
use Modules\Teacher\Interfaces\Repositories\TeacherRepositoryInterface;
use Modules\Teacher\Models\TeacherProfile;

class CreateTeacherAction extends BaseAction
{
    public function __construct(
        protected TeacherRepositoryInterface $repository,
    ) {}

    public function execute(mixed $input): TeacherProfile
    {
        $dto = $input;

        return $this->executeWithTransaction(function () use ($dto): TeacherProfile {
            if ($this->repository->existsByEmail($dto->email)) {
                throw TeacherOperationException::emailAlreadyExists();
            }

            if ($dto->employeeId && $this->repository->existsByEmployeeId($dto->employeeId)) {
                throw TeacherOperationException::employeeIdAlreadyExists();
            }

            $user = User::create([
                ...$dto->toUserArray(),
                'role' => UserRole::Teacher->value,
            ]);

            $user->assignRole('teacher');

            $teacher = $this->repository->createFromDTO($dto, auth()->id());
            $teacher->user_id = $user->id;
            $teacher->save();

            return $teacher->load('user');
        });
    }
}
