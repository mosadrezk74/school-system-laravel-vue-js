<?php

namespace Modules\Meeting\Repositories;

use Modules\Meeting\DTOs\CreateMeetingDTO;
use Modules\Meeting\DTOs\UpdateMeetingDTO;
use Modules\Meeting\Models\Meeting;

/**
 * Data access only: no validation, no business rules, no transactions.
 * Callers (Actions) own the transaction boundary and business checks.
 */
class MeetingRepository implements MeetingRepositoryInterface
{
    public function findById(int $id): ?Meeting
    {
        return Meeting::query()->find($id);
    }

    public function findByIdOrFail(int $id): Meeting
    {
        return Meeting::query()->findOrFail($id);
    }

    public function roomNameExists(string $roomName): bool
    {
        return Meeting::withTrashed()->where('room_name', $roomName)->exists();
    }

    public function create(CreateMeetingDTO $dto, string $uuid, string $roomName, string $meetingUrl, ?int $actorId = null): Meeting
    {
        return Meeting::create([
            ...$dto->toArray(),
            'uuid' => $uuid,
            'room_name' => $roomName,
            'meeting_url' => $meetingUrl,
            'status' => 'scheduled',
            'created_by' => $actorId,
            'updated_by' => $actorId,
        ]);
    }

    public function update(Meeting $meeting, UpdateMeetingDTO $dto, ?int $actorId = null): Meeting
    {
        $meeting->update([
            ...$dto->toArray(),
            'updated_by' => $actorId,
        ]);

        return $meeting->refresh();
    }

    public function updateStatus(Meeting $meeting, string $status, ?int $actorId = null): Meeting
    {
        $meeting->update([
            'status' => $status,
            'updated_by' => $actorId,
        ]);

        return $meeting->refresh();
    }

    public function delete(Meeting $meeting): bool
    {
        return (bool) $meeting->delete();
    }

    public function restore(Meeting $meeting): bool
    {
        return (bool) $meeting->restore();
    }
}
