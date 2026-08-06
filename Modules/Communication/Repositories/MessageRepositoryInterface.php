<?php

namespace Modules\Communication\Repositories;

use Modules\Communication\DTOs\CreateMessageDTO;
use Modules\Communication\Models\BroadcastMessage;

interface MessageRepositoryInterface
{
    public function findById(int $id): ?BroadcastMessage;

    public function findByIdOrFail(int $id): BroadcastMessage;

    public function create(CreateMessageDTO $dto, int $senderId): BroadcastMessage;

    public function markSent(BroadcastMessage $message): BroadcastMessage;
}
