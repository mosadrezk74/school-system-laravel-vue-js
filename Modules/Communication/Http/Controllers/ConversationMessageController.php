<?php

namespace Modules\Communication\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Modules\Communication\Actions\MarkConversationReadAction;
use Modules\Communication\Actions\SendMessageAction;
use Modules\Communication\DTOs\SendMessageDTO;
use Modules\Communication\Http\Requests\SendMessageRequest;
use Modules\Communication\Models\Conversation;
use Modules\Core\Abstracts\BaseInertiaController;

/**
 * Writing into a conversation.
 *
 * Reading is ConversationController's job — messages arrive as a prop of the
 * chat page rather than through an endpoint of their own, so there is one
 * membership-checked path to a thread's contents instead of two.
 */
class ConversationMessageController extends BaseInertiaController
{
    public function store(
        SendMessageRequest $request,
        Conversation $conversation,
        SendMessageAction $action,
    ): RedirectResponse {
        $this->authorize('sendMessage', $conversation);

        $action->execute([
            'conversation' => $conversation,
            'dto' => SendMessageDTO::fromRequest($request),
        ]);

        /*
         * Back to the thread rather than a flash toast: the new message is the
         * feedback, and a toast on every send would be noise.
         */
        return redirect()->route('communication.chat.show', ['conversation' => $conversation]);
    }

    /**
     * Marks the thread read for the signed-in participant.
     *
     * Explicit rather than a side effect of `show`, so paging back through
     * history never clears a badge the user meant to keep.
     */
    public function markRead(Conversation $conversation, MarkConversationReadAction $action): RedirectResponse
    {
        $this->authorize('markRead', $conversation);

        $action->execute($conversation);

        return back();
    }
}
