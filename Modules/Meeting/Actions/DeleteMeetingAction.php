<?php

namespace Modules\Meeting\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Meeting\Models\Meeting;
use Modules\Meeting\Repositories\MeetingRepositoryInterface;

class DeleteMeetingAction extends BaseAction
{
    public function __construct(
        protected MeetingRepositoryInterface $repository,
    ) {}

    /**
     * @param  Meeting  $input
     */
    public function execute(mixed $input): bool
    {
        /** @var Meeting $meeting */
        $meeting = $input;

        return $this->executeWithTransaction(function () use ($meeting): bool {
            return $this->repository->delete($meeting);
        });
    }
}
