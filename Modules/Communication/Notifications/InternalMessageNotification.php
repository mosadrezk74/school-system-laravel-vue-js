<?php

namespace Modules\Communication\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Modules\Communication\Models\BroadcastMessage;
use Modules\Setting\Services\LanguageService;

class InternalMessageNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected BroadcastMessage $message,
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
        return [
            'message_id' => $this->message->id,
            'title' => $this->message->getTranslation('title', $this->localeFor($notifiable)),
            'body' => $this->message->getTranslation('body', $this->localeFor($notifiable)),
            'sender_id' => $this->message->sender_id,
        ];
    }

    /**
     * Each user reads the copy in their own language; the school default
     * covers users who never picked one.
     */
    private function localeFor(mixed $notifiable): string
    {
        $locale = is_object($notifiable) ? ($notifiable->language ?? null) : null;

        return $locale ?: app(LanguageService::class)->defaultLocale();
    }
}
