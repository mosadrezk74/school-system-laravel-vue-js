<?php

namespace Modules\Communication\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Communication\Enums\MessageType;
use Modules\Communication\Models\Conversation;
use Modules\Communication\Models\Message;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Core\Support\Filters\DateRangeFilter;
use Modules\Core\Support\Filters\DerivedFilter;
use Modules\Core\Support\Filters\EnumFilter;
use Modules\Core\Support\Filters\ExactFilter;
use Modules\Core\Support\Filters\Filter;

/**
 * Messages inside one conversation.
 *
 * The conversation is a constructor argument, never request input: the caller
 * has already resolved it through a membership-checked route binding, so no
 * combination of query-string values can move this query to another thread.
 */
class ConversationMessageQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $searchableFields = ['message'];

    /** @var array<string, array<int, string>> */
    protected array $searchableRelations = [
        'sender' => ['name'],
    ];

    /** @var array<int, string> */
    protected array $sortableFields = ['sent_at', 'created_at'];

    protected string $defaultSortField = 'sent_at';

    public function __construct(private readonly Conversation $conversation)
    {
        parent::__construct();
    }

    protected function baseQuery(): Builder
    {
        return Message::query()
            ->where('conversation_id', $this->conversation->id)
            ->with(['sender:id,name,role', 'media']);
    }

    /**
     * @return array<string, Filter>
     */
    protected function allowedFilters(): array
    {
        return [
            'sender_id' => ExactFilter::make()->label('communication.chat.sender'),
            'message_type' => EnumFilter::make(MessageType::class)->label('communication.chat.messageType'),
            'date' => DateRangeFilter::make()->column('sent_at')->label('communication.chat.date'),
            /* Attachment presence lives in `media`, not in a column here. */
            'has_attachments' => DerivedFilter::boolean()->label('communication.chat.hasAttachments'),
        ];
    }

    protected function applyFilters(): Builder
    {
        $query = parent::applyFilters();

        $this->applyHasAttachments($query);

        return $query;
    }

    /**
     * Attachment presence lives in the polymorphic `media` table rather than
     * in a column, so it is an existence check against this message's own
     * collection — not merely "has any media row", which would also match a
     * different collection if one is ever added to this model.
     *
     * @param  Builder<Message>  $query
     */
    private function applyHasAttachments(Builder $query): void
    {
        $value = $this->filters['has_attachments'] ?? null;

        if ($value === null || $value === '') {
            return;
        }

        $wanted = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if ($wanted === null) {
            return;
        }

        $collection = (string) config('communication.attachments.collection', 'message_attachments');

        $constraint = fn (Builder $media) => $media->where('collection_name', $collection);

        $wanted
            ? $query->whereHas('media', $constraint)
            : $query->whereDoesntHave('media', $constraint);
    }
}
