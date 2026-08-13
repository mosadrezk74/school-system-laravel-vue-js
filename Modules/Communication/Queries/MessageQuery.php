<?php

namespace Modules\Communication\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Communication\Models\BroadcastMessage;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Core\Support\Filters\DateRangeFilter;
use Modules\Core\Support\Filters\ExactFilter;
use Modules\Core\Support\Filters\Filter;
use Modules\Core\Support\Filters\NumericFilter;
use Modules\Core\Support\Filters\TranslatableFilter;

class MessageQuery extends BaseQuery
{
    /**
     * `title` and `body` are translatable JSON columns.
     *
     * @var array<int, string>
     */
    protected array $translatableSearchFields = ['title', 'body'];

    /** @var array<string, array<int, string>> */
    protected array $searchableRelations = [
        'sender' => ['name'],
    ];

    /** @var array<int, string> */
    protected array $sortableFields = ['sent_at', 'scheduled_at', 'status', 'created_at'];

    protected function baseQuery(): Builder
    {
        return BroadcastMessage::query()->with(['sender']);
    }

    /**
     * `target_ids` is a JSON list of recipient keys, so it is not filterable —
     * the recipient *scope* (`target_type`) is the axis that describes who a
     * message went to.
     *
     * `status` and `target_type` are ExactFilters: their domains are defined by
     * RecipientResolverService::TYPES and the send pipeline rather than by a
     * PHP enum, and inventing enums for them here would be a domain change
     * rather than a filtering one.
     *
     * @return array<string, Filter>
     */
    protected function allowedFilters(): array
    {
        return [
            'status' => ExactFilter::make()->label('communication.status'),
            'target_type' => ExactFilter::make()->label('communication.targetType'),
            'sender_id' => ExactFilter::make()->label('communication.sender')->nullable(),
            'title' => TranslatableFilter::make()->label('communication.title'),
            'sent' => DateRangeFilter::make()->column('sent_at')->label('communication.sentAt'),
            'scheduled' => DateRangeFilter::make()->column('scheduled_at')->label('communication.scheduledAt'),
            'failed_count' => NumericFilter::make()->label('communication.failedCount'),
        ];
    }
}
