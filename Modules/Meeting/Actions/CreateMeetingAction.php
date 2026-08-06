<?php

namespace Modules\Meeting\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Meeting\DTOs\CreateMeetingDTO;
use Modules\Meeting\Models\Meeting;
use Modules\Meeting\Repositories\MeetingRepositoryInterface;
use Modules\Meeting\Services\JitsiRoomFactory;

class CreateMeetingAction extends BaseAction
{
    public function __construct(
        protected MeetingRepositoryInterface $repository,
        protected JitsiRoomFactory $roomFactory,
    ) {}

    /**
     * @param  CreateMeetingDTO  $input
     */
    public function execute(mixed $input): Meeting
    {
        /** @var CreateMeetingDTO $dto */
        $dto = $input;

        return $this->executeWithTransaction(function () use ($dto): Meeting {
            $room = $this->roomFactory->make();

            return $this->repository->create($dto, $room['uuid'], $room['room_name'], $room['meeting_url'], auth()->id());
        });
    }
}
