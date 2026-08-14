<?php

namespace Modules\Communication\Enums;

use Modules\Core\Traits\BaseEnum;

/**
 * The shape of a conversation, stored on `conversations.type` (varchar 15).
 *
 * Direct conversations are identified by their participant pair rather than by
 * a name, which is why StartConversationAction reuses an existing one instead
 * of letting the same two people accumulate duplicate threads.
 */
enum ConversationType: string
{
    use BaseEnum;

    case Direct = 'direct';
    case Group = 'group';

    public function label(): string
    {
        return __("communication::chat.types.{$this->value}");
    }

    public function isDirect(): bool
    {
        return $this === self::Direct;
    }
}
