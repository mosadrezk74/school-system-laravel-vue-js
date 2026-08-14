<?php

namespace Modules\Communication\Transformers;

use Illuminate\Http\Request;
use Modules\Communication\Models\Conversation;
use Modules\Communication\Models\ConversationParticipant;
use Modules\Core\Abstracts\BaseResource;

/**
 * @mixin Conversation
 */
class ConversationResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $viewerId = (int) ($request->user()?->id ?? 0);

        return [
            'id' => $this->id,
            'type' => $this->type?->value,

            /*
             * A direct thread has no stored name — it is titled by whoever the
             * viewer is talking to, so the same row reads differently for each
             * of the two participants.
             */
            'title' => $this->resolveTitle($viewerId),

            'name' => $this->name,
            'last_message_at' => $this->last_message_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),

            /*
             * Supplied by ConversationQuery's withCount subquery, which is
             * already scoped to this viewer's last_read_at. Falling back to 0
             * keeps the contract stable for the show page, which loads a
             * single conversation without the aggregate.
             */
            'unread_count' => (int) ($this->unread_count ?? 0),

            'participants' => $this->whenLoaded(
                'activeParticipants',
                fn () => ConversationParticipantResource::collection($this->activeParticipants),
                [],
            ),

            'last_message' => $this->whenLoaded(
                'latestMessage',
                fn () => $this->latestMessage
                    ? new ConversationMessageResource($this->latestMessage)
                    : null,
            ),
        ];
    }

    /**
     * The other participant's name for a direct thread, the stored name for a
     * group.
     *
     * Falls back to the group label rather than to an empty string so a thread
     * whose counterpart has been deleted still renders a row the user can
     * open and read their own history in.
     */
    private function resolveTitle(int $viewerId): ?string
    {
        if (! $this->isDirect()) {
            return $this->name;
        }

        if (! $this->relationLoaded('activeParticipants')) {
            return $this->name;
        }

        $other = $this->activeParticipants
            ->first(fn (ConversationParticipant $participant): bool => (int) $participant->user_id !== $viewerId);

        return $other?->user?->name ?? __('communication::chat.unknown_participant');
    }
}
