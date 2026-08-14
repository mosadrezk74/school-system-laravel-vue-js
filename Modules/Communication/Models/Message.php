<?php

namespace Modules\Communication\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Modules\Communication\Enums\MessageType;
use Modules\Core\Abstracts\BaseModel;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * A chat message inside a Conversation.
 *
 * Not to be confused with BroadcastMessage, which is the one-way announcement
 * this module also owns — the `Message*` class names in this module (
 * MessageController, MessageQuery, MessageResource) belong to *that* domain,
 * which is why the chat's own classes are named Conversation* and
 * ConversationMessage*.
 *
 * @property int $id
 * @property int $conversation_id
 * @property int $sender_id
 * @property string|null $message
 * @property MessageType $message_type
 * @property Carbon $sent_at
 */
class Message extends BaseModel implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'messages';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'sent_at' => 'datetime',
            'message_type' => MessageType::class,
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Attachments live on a private disk with no public URL.
     *
     * Media Library's default disk is `public`, which would place every file
     * under storage/app/public and hand out a guessable URL that bypasses
     * every membership check in this module. Pinning the collection's disk
     * here is what keeps MessageAttachmentController the only way in.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection($this->attachmentCollection())
            ->useDisk((string) config('communication.attachments.disk', 'local'));
    }

    public function attachmentCollection(): string
    {
        return (string) config('communication.attachments.collection', 'message_attachments');
    }

    /**
     * Deliberately not named `attachments()`.
     *
     * Eloquent resolves an unknown property by calling the same-named method
     * and expecting a Relation back, so a method called `attachments()`
     * returning a plain Collection would make `$message->attachments` throw —
     * a trap for the next person to touch this model. The name says "media" to
     * keep that door shut.
     *
     * @return Collection<int, Media>
     */
    public function attachmentMedia(): Collection
    {
        return $this->getMedia($this->attachmentCollection());
    }

    public function hasAttachments(): bool
    {
        return $this->attachmentMedia()->isNotEmpty();
    }
}
