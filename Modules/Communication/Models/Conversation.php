<?php

namespace Modules\Communication\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Modules\Communication\Enums\ConversationType;
use Modules\Core\Abstracts\BaseModel;

/**
 * @property int $id
 * @property ConversationType $type
 * @property string|null $name
 * @property Carbon|null $last_message_at
 * @property int|null $created_by
 */
class Conversation extends BaseModel
{
    protected $table = 'conversations';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
            'type' => ConversationType::class,
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ConversationParticipant::class, 'conversation_id');
    }

    /**
     * Participants who have not left. `is_active` is the per-person flag, so
     * one participant leaving a group never hides the thread from the rest.
     */
    public function activeParticipants(): HasMany
    {
        return $this->participants()->where('is_active', true);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_participants', 'conversation_id', 'user_id')
            ->withPivot(['last_read_at', 'is_active'])
            ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'conversation_id');
    }

    /**
     * The single most recent message, for the conversation list's preview.
     *
     * `ofMany` compiles to a correlated subquery, so eager-loading this across
     * a page of conversations stays two queries rather than one per row.
     */
    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class, 'conversation_id')->latestOfMany('sent_at');
    }

    /**
     * Conversations the given user actually belongs to.
     *
     * Every read path starts here. Scoping by membership at the query level —
     * rather than fetching by id and checking afterwards — is what makes an
     * unauthorised conversation id return "not found" instead of leaking that
     * the row exists.
     *
     * @param  Builder<Conversation>  $query
     * @return Builder<Conversation>
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->whereHas(
            'participants',
            fn (Builder $participants) => $participants
                ->where('user_id', $userId)
                ->where('is_active', true),
        );
    }

    public function participantFor(int $userId): ?ConversationParticipant
    {
        return $this->participants()
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->first();
    }

    public function hasParticipant(int $userId): bool
    {
        return $this->participants()
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->exists();
    }

    public function isDirect(): bool
    {
        return $this->type === ConversationType::Direct;
    }
}
