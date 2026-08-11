<?php

namespace Modules\Assignment\Actions;

use Modules\Assignment\DTOs\CreateAssignmentDTO;
use Modules\Assignment\Exceptions\AssignmentOperationException;
use Modules\Assignment\Models\Assignment;
use Modules\Assignment\Repositories\AssignmentRepositoryInterface;
use Modules\Core\Abstracts\BaseAction;
use Modules\SchoolClass\Models\ClassSubject;

class CreateAssignmentAction extends BaseAction
{
    public function __construct(
        protected AssignmentRepositoryInterface $repository,
    ) {}

    /**
     * @param  CreateAssignmentDTO  $input
     */
    public function execute(mixed $input): Assignment
    {
        /** @var CreateAssignmentDTO $dto */
        $dto = $input;

        $this->assertTeachingAssignmentExists($dto);

        return $this->executeWithTransaction(function () use ($dto): Assignment {
            return $this->repository->create($dto, auth()->id());
        });
    }

    /**
     * The form request already rejects a mismatched triple; repeating the check
     * here keeps the rule with the operation, so any caller reaching the Action
     * directly cannot file an assignment against a teacher who does not teach
     * that subject to that class.
     */
    private function assertTeachingAssignmentExists(CreateAssignmentDTO $dto): void
    {
        $exists = ClassSubject::query()
            ->where('class_id', $dto->classId)
            ->where('subject_id', $dto->subjectId)
            ->where('teacher_id', $dto->teacherId)
            ->exists();

        if (! $exists) {
            throw AssignmentOperationException::invalidTeachingAssignment();
        }
    }
}
