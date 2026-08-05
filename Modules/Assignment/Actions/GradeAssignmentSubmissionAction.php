<?php

namespace Modules\Assignment\Actions;

use Modules\Assignment\DTOs\GradeAssignmentSubmissionDTO;
use Modules\Assignment\Models\AssignmentSubmission;
use Modules\Assignment\Repositories\AssignmentSubmissionRepositoryInterface;
use Modules\Core\Abstracts\BaseAction;

class GradeAssignmentSubmissionAction extends BaseAction
{
    public function __construct(
        protected AssignmentSubmissionRepositoryInterface $repository,
    ) {}

    /**
     * @param  array{submission: AssignmentSubmission, dto: GradeAssignmentSubmissionDTO}  $input
     */
    public function execute(mixed $input): AssignmentSubmission
    {
        /** @var AssignmentSubmission $submission */
        $submission = $input['submission'];
        /** @var GradeAssignmentSubmissionDTO $dto */
        $dto = $input['dto'];

        return $this->executeWithTransaction(function () use ($submission, $dto): AssignmentSubmission {
            return $this->repository->update($submission, $dto);
        });
    }
}
