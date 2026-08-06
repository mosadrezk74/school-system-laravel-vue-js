<?php

namespace Modules\Meeting\Services;

use Illuminate\Support\Str;
use Modules\Meeting\Repositories\MeetingRepositoryInterface;

class JitsiRoomFactory
{
    private const BASE_URL = 'https://meet.etqan.cloud';

    public function __construct(
        protected MeetingRepositoryInterface $repository,
    ) {}

    /**
     * @return array{uuid: string, room_name: string, meeting_url: string}
     */
    public function make(): array
    {
        $roomName = $this->generateUniqueRoomName();

        return [
            'uuid' => (string) Str::uuid(),
            'room_name' => $roomName,
            'meeting_url' => self::BASE_URL.'/'.$roomName,
        ];
    }

    private function generateUniqueRoomName(): string
    {
        do {
            $roomName = Str::lower(Str::random(12));
        } while ($this->repository->roomNameExists($roomName));

        return $roomName;
    }
}
