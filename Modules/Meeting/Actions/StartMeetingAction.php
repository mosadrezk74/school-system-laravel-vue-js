<?php

namespace Modules\Meeting\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Meeting\Exceptions\MeetingOperationException;
use Modules\Meeting\Models\Meeting;
use Modules\Meeting\Repositories\MeetingRepositoryInterface;

class StartMeetingAction extends BaseAction
{
    public function __construct(
        protected MeetingRepositoryInterface $repository,
    ) {}

    /**
     * @param  Meeting  $input
     */
    public function execute(mixed $input): Meeting
    {
        /** @var Meeting $meeting */
        $meeting = $input;

        return $this->executeWithTransaction(function () use ($meeting): Meeting {
            if ($meeting->status !== 'scheduled') {
                throw MeetingOperationException::invalidStatusTransition();
            }

            return $this->repository->updateStatus($meeting, 'live', auth()->id());
        });
    }
}
