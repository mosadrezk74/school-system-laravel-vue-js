<?php

namespace Modules\Communication\Actions;

use App\Models\User;
use Modules\Communication\DTOs\SendMessageDTO;
use Modules\Communication\Exceptions\ConversationOperationException;
use Modules\Communication\Models\Conversation;
use Modules\Communication\Models\Message;
use Modules\Communication\Notifications\NewChatMessageNotification;
use Modules\Communication\Services\MessageAttachmentService;
use Modules\Core\Abstracts\BaseAction;

class SendMessageAction extends BaseAction
{
    public function __construct(
        private readonly MessageAttachmentService $attachments,
    ) {}

    /**
     * @param  array{conversation: Conversation, dto: SendMessageDTO}  $input
     */
    public function execute(mixed $input): Message
    {
        /** @var Conversation $conversation */
        $conversation = $input['conversation'];

        /** @var SendMessageDTO $dto */
        $dto = $input['dto'];

        $senderId = (int) auth()->id();

        /*
         * Membership is re-checked here and not only in the policy. The Action
         * is also reachable from StartConversationAction and from any future
         * caller, and "who may write into this conversation" is a rule of the
         * conversation rather than of the HTTP layer.
         */
        if (! $conversation->hasParticipant($senderId)) {
            throw ConversationOperationException::notAParticipant();
        }

        if ($dto->isEmpty()) {
            throw ConversationOperationException::emptyMessage();
        }

        $message = $this->executeWithTransaction(function () use ($conversation, $dto, $senderId): Message {
            $message = Message::query()->create([
                'conversation_id' => $conversation->id,
                'sender_id' => $senderId,
                'message' => $dto->body,
                'message_type' => $dto->type(),
                'sent_at' => now(),
            ]);

            if ($dto->hasAttachments()) {
                $this->attachments->attach($message, $dto->attachments);
            }

            /*
             * Denormalised so the conversation list can sort and preview
             * without a correlated subquery per row.
             */
            $conversation->forceFill(['last_message_at' => $message->sent_at])->save();

            // The sender has read their own message by definition.
            $conversation->participants()
                ->where('user_id', $senderId)
                ->update(['last_read_at' => $message->sent_at]);

            return $message;
        });

        $this->notifyOtherParticipants($conversation, $message, $senderId);

        return $message;
    }

    /**
     * Notified after the transaction commits, so a rolled-back send never
     * produces a notification for a message that does not exist.
     */
    private function notifyOtherParticipants(Conversation $conversation, Message $message, int $senderId): void
    {
        $recipientIds = $conversation->participants()
            ->where('is_active', true)
            ->where('user_id', '!=', $senderId)
            ->pluck('user_id');

        if ($recipientIds->isEmpty()) {
            return;
        }

        /*
         * Loaded once here rather than inside the notification, which reads
         * both while building its payload — otherwise a group of thirty would
         * issue sixty extra queries to fetch the same sender and the same
         * media rows over and over.
         */
        $message->loadMissing(['sender:id,name', 'media']);

        User::query()
            ->whereIn('id', $recipientIds)
            ->where('is_active', true)
            ->select(['id', 'language'])
            ->chunkById(200, function ($users) use ($message): void {
                foreach ($users as $user) {
                    $user->notify(new NewChatMessageNotification($message));
                }
            });
    }
}
