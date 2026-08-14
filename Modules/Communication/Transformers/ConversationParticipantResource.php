<?php

namespace Modules\Communication\Transformers;

use Illuminate\Http\Request;
use Modules\Communication\Models\ConversationParticipant;
use Modules\Core\Abstracts\BaseResource;

/**
 * A member of a conversation.
 *
 * Carries the account's display name and coarse role only. Nothing from the
 * user's profile — email, phone, national id, medical or fee information —
 * belongs in a chat payload that every other participant can read.
 *
 * @mixin ConversationParticipant
 */
class ConversationParticipantResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->user_id,
            'name' => $this->whenLoaded('user', fn () => $this->user?->name),
            'role' => $this->whenLoaded('user', fn () => $this->user?->role?->value),
            'is_active' => (bool) $this->is_active,
            'joined_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
