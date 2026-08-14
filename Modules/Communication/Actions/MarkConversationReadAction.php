<?php

namespace Modules\Communication\Actions;

use Modules\Communication\Exceptions\ConversationOperationException;
use Modules\Communication\Models\Conversation;
use Modules\Core\Abstracts\BaseAction;

/**
 * Marks a conversation read for the signed-in participant only.
 *
 * Read state is per person, held in `conversation_participants.last_read_at`.
 * The legacy `messages.is_read` boolean is deliberately untouched: a single
 * flag on a row that several people can read cannot express "Ali has seen it
 * but Sara has not", and writing to it would make one participant opening a
 * thread clear everyone else's badge.
 *
 * This is an explicit endpoint rather than a side effect of fetching messages,
 * so scrolling back through history never silently clears a badge the user
 * still wants.
 */
class MarkConversationReadAction extends BaseAction
{
    /**
     * @param  Conversation  $input
     */
    public function execute(mixed $input): void
    {
        /** @var Conversation $conversation */
        $conversation = $input;

        $userId = (int) auth()->id();

        $updated = $conversation->participants()
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->update(['last_read_at' => now()]);

        if ($updated === 0) {
            throw ConversationOperationException::notAParticipant();
        }
    }
}
