<?php

namespace Modules\Communication\Policies;

use App\Models\User;
use Modules\Communication\Models\Conversation;

/**
 * Chat authorisation is two independent gates, and both must pass.
 *
 *   permission — may this account use chat at all? (`message.view` /
 *                `message.create`, already granted to admin, teacher, staff,
 *                student and guardian by RolePermissionSeeder)
 *   membership — is this account a participant of *this* conversation?
 *
 * The second is the one that matters. Holding `message.view` says nothing
 * about which threads may be read, so every per-conversation ability below
 * checks the participant rows and never the role.
 *
 * Note the absence of an admin escape hatch: an administrator who is not a
 * participant cannot read a family's conversation with a teacher. Owner still
 * bypasses via the Gate::before rule the application already defines, which is
 * the project's existing convention rather than a decision taken here — it is
 * called out in the report as something to confirm.
 */
class ConversationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('message.view');
    }

    public function view(User $user, Conversation $conversation): bool
    {
        return $user->can('message.view') && $conversation->hasParticipant($user->id);
    }

    public function create(User $user): bool
    {
        return $user->can('message.create');
    }

    /** Writing into a thread needs both the ability and membership of it. */
    public function sendMessage(User $user, Conversation $conversation): bool
    {
        return $user->can('message.create') && $conversation->hasParticipant($user->id);
    }

    /** Reading an attachment is exactly as privileged as reading the thread. */
    public function downloadAttachment(User $user, Conversation $conversation): bool
    {
        return $this->view($user, $conversation);
    }

    public function markRead(User $user, Conversation $conversation): bool
    {
        return $this->view($user, $conversation);
    }
}
