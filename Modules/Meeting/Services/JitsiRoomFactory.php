<?php

namespace Modules\Meeting\Services;

use Illuminate\Support\Str;
use Modules\Meeting\Repositories\MeetingRepositoryInterface;

class JitsiRoomFactory
{
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
            'meeting_url' => $this->baseUrl().'/'.$roomName,
        ];
    }

    /**
     * The bare host, as the Jitsi External API expects it (no scheme).
     */
    public function domain(): string
    {
        return (string) config('meeting.jitsi.domain', 'meet.etqan.cloud');
    }

    public function baseUrl(): string
    {
        return 'https://'.$this->domain();
    }

    private function generateUniqueRoomName(): string
    {
        do {
            $roomName = Str::lower(Str::random(12));
        } while ($this->repository->roomNameExists($roomName));

        return $roomName;
    }
}
