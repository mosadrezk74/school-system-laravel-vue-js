<?php

namespace Modules\Communication\Repositories;

use Modules\Communication\DTOs\CreateMessageDTO;
use Modules\Communication\Models\BroadcastMessage;

/**
 * Data access only: no validation, no business rules, no transactions.
 * Callers (Actions) own the transaction boundary and business checks.
 */
class MessageRepository implements MessageRepositoryInterface
{
    public function findById(int $id): ?BroadcastMessage
    {
        return BroadcastMessage::query()->find($id);
    }

    public function findByIdOrFail(int $id): BroadcastMessage
    {
        return BroadcastMessage::query()->findOrFail($id);
    }

    public function create(CreateMessageDTO $dto, int $senderId): BroadcastMessage
    {
        return BroadcastMessage::create([
            ...$dto->toArray(),
            'sender_id' => $senderId,
            'status' => 'pending',
        ]);
    }

    public function markSent(BroadcastMessage $message): BroadcastMessage
    {
        $message->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        return $message->refresh();
    }
}
