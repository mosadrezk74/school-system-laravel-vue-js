<?php

namespace Modules\Communication\Services;

use Modules\Communication\Actions\CreateMessageAction;
use Modules\Communication\DTOs\CreateMessageDTO;
use Modules\Communication\DTOs\SendEmailBroadcastDTO;
use Modules\Communication\Models\BroadcastMessage;

/**
 * Entry point for other modules that need to communicate with users without
 * wiring Communication's Actions/DTOs themselves (e.g. a future Meeting
 * reminder). Communication's own controllers call the Actions directly,
 * matching this project's usual thin-controller convention.
 */
class CommunicationService
{
    public function __construct(
        protected CreateMessageAction $createMessageAction,
        protected EmailSenderService $emailSender,
    ) {}

    public function sendMessage(CreateMessageDTO $dto): BroadcastMessage
    {
        return $this->createMessageAction->execute($dto);
    }

    public function sendEmailBroadcast(SendEmailBroadcastDTO $dto): void
    {
        $this->emailSender->send($dto);
    }
}
