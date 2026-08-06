<?php

namespace Modules\Meeting\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Meeting\Exceptions\MeetingOperationException;
use Modules\Meeting\Models\Meeting;
use Modules\Meeting\Repositories\MeetingRepositoryInterface;

class UpdateMeetingAction extends BaseAction
{
    public function __construct(
        protected MeetingRepositoryInterface $repository,
    ) {}

    public function execute(mixed $input): Meeting
    {
        $meeting = $input['meeting'];
        $dto = $input['dto'];

        return $this->executeWithTransaction(function () use ($meeting, $dto): Meeting {
            if ($meeting->status !== 'scheduled') {
                throw MeetingOperationException::invalidStatusTransition();
            }

            return $this->repository->update($meeting, $dto, auth()->id());
        });
    }
}
