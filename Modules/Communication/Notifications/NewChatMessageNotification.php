<?php

namespace Modules\Communication\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Modules\Communication\Models\Message;
use Modules\Setting\Services\LanguageService;

/**
 * Tells a participant a message arrived.
 *
 * Database channel only, matching InternalMessageNotification — the project
 * has no broadcast driver configured, and adding one for chat would mean
 * introducing infrastructure this task is explicitly not to introduce.
 *
 * The message body is deliberately not copied into the payload. A notification
 * row is readable wherever notifications are listed, and duplicating private
 * conversation text into it would put that text outside the membership checks
 * that guard the conversation itself. The recipient gets a pointer and follows
 * it through the authorised route.
 */
class NewChatMessageNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Message $message,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(mixed $notifiable): array
    {
        $locale = $this->localeFor($notifiable);

        return [
            'type' => 'chat_message',
            'conversation_id' => $this->message->conversation_id,
            'message_id' => $this->message->id,
            'sender_id' => $this->message->sender_id,
            'sender_name' => $this->message->sender?->name,
            'has_attachments' => $this->message->hasAttachments(),
            'title' => __('communication::chat.notification.title', [], $locale),
            'body' => __('communication::chat.notification.body', [
                'sender' => $this->message->sender?->name ?? '',
            ], $locale),
        ];
    }

    /** Each user reads the copy in their own language. */
    private function localeFor(mixed $notifiable): string
    {
        $locale = is_object($notifiable) ? ($notifiable->language ?? null) : null;

        return $locale ?: app(LanguageService::class)->defaultLocale();
    }
}
