<?php

namespace Modules\Communication\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Communication\Enums\ConversationType;
use Modules\Communication\Models\Conversation;
use Modules\Communication\Models\ConversationParticipant;
use Modules\Communication\Models\Message;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Core\Support\Filters\DateRangeFilter;
use Modules\Core\Support\Filters\DerivedFilter;
use Modules\Core\Support\Filters\EnumFilter;
use Modules\Core\Support\Filters\Filter;

/**
 * The signed-in user's conversation list.
 *
 * The membership constraint is applied in `baseQuery()` rather than as a
 * filter, which means it cannot be cleared, overridden or reached around from
 * the query string — every other clause narrows a set that is already limited
 * to this user's own threads.
 */
class ConversationQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $searchableFields = ['name'];

    /** @var array<int, string> */
    protected array $sortableFields = ['last_message_at', 'created_at', 'name'];

    protected string $defaultSortField = 'last_message_at';

    public function __construct(private readonly int $userId)
    {
        parent::__construct();
    }

    protected function baseQuery(): Builder
    {
        /*
         * The join *is* the membership check: an inner join carrying this
         * user's id drops every conversation they do not belong to before any
         * other clause runs. It also puts `last_read_at` in scope for the
         * unread subqueries below, which is why membership is expressed this
         * way here rather than through Conversation::scopeForUser().
         *
         * Column order matters: `select()` must precede `withCount()`, because
         * a later select() would discard the count's subquery column.
         */
        return Conversation::query()
            ->join('conversation_participants', function ($join): void {
                $join->on('conversation_participants.conversation_id', '=', 'conversations.id')
                    ->where('conversation_participants.user_id', '=', $this->userId);
            })
            ->select('conversations.*')
            ->withCount([
                /*
                 * Messages this user has not seen: everything sent by someone
                 * else since their own last_read_at. A correlated subquery, so
                 * a page of conversations stays one query rather than one per
                 * row.
                 */
                'messages as unread_count' => fn (Builder $query) => $query
                    ->where('sender_id', '!=', $this->userId)
                    ->where(function (Builder $unread): void {
                        $unread
                            ->whereNull('conversation_participants.last_read_at')
                            ->orWhereColumn('messages.sent_at', '>', 'conversation_participants.last_read_at');
                    }),
            ])
            ->with([
                /* `role` is selected because ConversationMessageResource emits it. */
                'latestMessage.sender:id,name,role',
                'latestMessage.media',
                'activeParticipants.user:id,name,role',
            ]);
    }

    /**
     * `unread` and `archived` are predicates, not columns — the first is a
     * comparison against this viewer's `last_read_at`, the second reads the
     * joined participant row. They are declared as DerivedFilters so they
     * still appear in the schema, still echo back through `activeFilters()`
     * and still sit inside the whitelist, while the constraint itself is
     * written in `applyFilters()` below where it can be expressed properly.
     *
     * @return array<string, Filter>
     */
    protected function allowedFilters(): array
    {
        return [
            'type' => EnumFilter::make(ConversationType::class)->label('communication.chat.type'),
            'activity' => DateRangeFilter::make()->column('last_message_at')->label('communication.chat.lastActivity'),
            'archived' => DerivedFilter::boolean()->label('communication.chat.archived'),
            'unread' => DerivedFilter::boolean()->label('communication.chat.unread'),
        ];
    }

    /**
     * Search also reaches participant names and message bodies, neither of
     * which is a column on `conversations`, so it extends the trait's
     * whitelist-driven search rather than replacing it.
     *
     * The added clauses are still closed: the columns are literals here, and
     * the term only ever arrives as a bound value.
     */
    public function search(?string $term): self
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $this;
        }

        $this->query->where(function (Builder $builder) use ($term): void {
            $builder
                ->where('conversations.name', 'LIKE', "%{$term}%")
                ->orWhereIn('conversations.id', ConversationParticipant::query()
                    ->select('conversation_id')
                    ->whereHas('user', fn ($user) => $user->where('name', 'LIKE', "%{$term}%")))
                ->orWhereIn('conversations.id', Message::query()
                    ->select('conversation_id')
                    ->where('message', 'LIKE', "%{$term}%"));
        });

        return $this;
    }

    /**
     * Applied after the declared filters so the derived predicates can read
     * the same flat input without inventing a second filtering mechanism.
     */
    protected function applyFilters(): Builder
    {
        $query = parent::applyFilters();

        $this->applyUnread($query);
        $this->applyArchived($query);

        return $query;
    }

    /**
     * @param  Builder<Conversation>  $query
     */
    private function applyUnread(Builder $query): void
    {
        $value = $this->filters['unread'] ?? null;

        if ($value === null || $value === '') {
            return;
        }

        $wantsUnread = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if ($wantsUnread === null) {
            return;
        }

        $hasUnread = fn (Builder $messages) => $messages
            ->where('sender_id', '!=', $this->userId)
            ->where(function (Builder $unread): void {
                $unread
                    ->whereNull('conversation_participants.last_read_at')
                    ->orWhereColumn('messages.sent_at', '>', 'conversation_participants.last_read_at');
            });

        $wantsUnread
            ? $query->whereHas('messages', $hasUnread)
            : $query->whereDoesntHave('messages', $hasUnread);
    }

    /**
     * Archiving is per participant — leaving a group must not remove it from
     * everyone else's list — so it reads the joined pivot rather than a column
     * on `conversations`.
     *
     * @param  Builder<Conversation>  $query
     */
    private function applyArchived(Builder $query): void
    {
        $value = $this->filters['archived'] ?? null;

        $archived = $value === null || $value === ''
            ? false
            : filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        $query->where('conversation_participants.is_active', ! ($archived ?? false));
    }
}
