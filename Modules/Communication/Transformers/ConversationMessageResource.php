<?php

namespace Modules\Communication\Transformers;

use Illuminate\Http\Request;
use Modules\Communication\Models\Message;
use Modules\Core\Abstracts\BaseResource;

/**
 * A chat message.
 *
 * Named for the conversation it belongs to because MessageResource in this
 * module already describes a BroadcastMessage — a production response contract
 * that is not renamed here.
 *
 * @mixin Message
 */
class ConversationMessageResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $viewerId = $request->user()?->id;

        return [
            'id' => $this->id,
            'conversation_id' => $this->conversation_id,
            'body' => $this->message,
            'type' => $this->message_type?->value,
            'sent_at' => $this->sent_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),

            /* Lets the page align the bubble without trusting a client guess. */
            'is_mine' => $viewerId !== null && (int) $this->sender_id === (int) $viewerId,

            'sender' => $this->whenLoaded('sender', fn () => $this->sender ? [
                'id' => $this->sender->id,
                'name' => $this->sender->name,
                'role' => $this->sender->role?->value,
            ] : null),

            /*
             * Read from the already-eager-loaded media relation rather than
             * through getMedia(), which would issue a query per message.
             */
            'attachments' => $this->whenLoaded(
                'media',
                fn () => MessageAttachmentResource::collection(
                    $this->media->where('collection_name', $this->attachmentCollection())->values(),
                ),
                [],
            ),
        ];
    }
}
