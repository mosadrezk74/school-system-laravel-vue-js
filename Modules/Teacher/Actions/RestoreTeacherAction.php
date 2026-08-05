<?php

namespace Modules\Teacher\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Teacher\Interfaces\Repositories\TeacherRepositoryInterface;

class RestoreTeacherAction extends BaseAction
{
    public function __construct(
        protected TeacherRepositoryInterface $repository,
    ) {}

    public function execute(mixed $input): bool
    {
        $teacher = $input;

        return $this->executeWithTransaction(function () use ($teacher): bool {
            $teacher->user->update(['is_active' => true]);

            return $this->repository->restore($teacher->id);
        });
    }
}
