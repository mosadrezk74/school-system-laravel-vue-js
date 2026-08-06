<?php

namespace Modules\Meeting\Repositories;

use Modules\Meeting\DTOs\CreateMeetingDTO;
use Modules\Meeting\DTOs\UpdateMeetingDTO;
use Modules\Meeting\Models\Meeting;

interface MeetingRepositoryInterface
{
    public function findById(int $id): ?Meeting;

    public function findByIdOrFail(int $id): Meeting;

    public function roomNameExists(string $roomName): bool;

    public function create(CreateMeetingDTO $dto, string $uuid, string $roomName, string $meetingUrl, ?int $actorId = null): Meeting;

    public function update(Meeting $meeting, UpdateMeetingDTO $dto, ?int $actorId = null): Meeting;

    public function updateStatus(Meeting $meeting, string $status, ?int $actorId = null): Meeting;

    public function delete(Meeting $meeting): bool;

    public function restore(Meeting $meeting): bool;
}
