<?php

namespace Modules\Communication\Enums;

use Modules\Core\Traits\BaseEnum;

/**
 * What a message carries, stored on `messages.message_type` (varchar 10).
 *
 * There is deliberately no `image` case. A message may carry several
 * attachments at once, and a mixed set of photos and PDFs would have no
 * truthful value here — whether a given file is previewable is a property of
 * that file's mime type, which MessageAttachmentResource derives per
 * attachment. A message-level `image` would only be right for the
 * single-attachment case and silently wrong for every other.
 */
enum MessageType: string
{
    use BaseEnum;

    case Text = 'text';
    case File = 'file';

    /** Written by the application (participant added, conversation renamed). */
    case System = 'system';

    public function label(): string
    {
        return __("communication::chat.messageTypes.{$this->value}");
    }

    /** Types a participant may author; System is application-written only. */
    public static function authorable(): array
    {
        return [self::Text, self::File];
    }
}
