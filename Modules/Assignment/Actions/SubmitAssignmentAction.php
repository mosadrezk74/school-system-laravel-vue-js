<?php

namespace Modules\Assignment\Actions;

use Carbon\Carbon;
use Modules\Assignment\DTOs\SubmitAssignmentDTO;
use Modules\Assignment\Exceptions\AssignmentOperationException;
use Modules\Assignment\Models\Assignment;
use Modules\Assignment\Models\AssignmentSubmission;
use Modules\Assignment\Repositories\AssignmentSubmissionRepositoryInterface;
use Modules\Core\Abstracts\BaseAction;

class SubmitAssignmentAction extends BaseAction
{
    public function __construct(
        protected AssignmentSubmissionRepositoryInterface $repository,
    ) {}

    /**
     * @param  SubmitAssignmentDTO  $input
     */
    public function execute(mixed $input): AssignmentSubmission
    {
        /** @var SubmitAssignmentDTO $dto */
        $dto = $input;

        return $this->executeWithTransaction(function () use ($dto): AssignmentSubmission {
            if ($this->repository->findByAssignmentAndStudent($dto->assignmentId, $dto->studentId)) {
                throw AssignmentOperationException::submissionAlreadyExists();
            }

            $assignment = Assignment::findOrFail($dto->assignmentId);

            $isLate = Carbon::parse($dto->submittedAt)->greaterThan($assignment->deadline);

            return $this->repository->create($dto, $isLate);
        });
    }
}
