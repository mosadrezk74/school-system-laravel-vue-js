<?php

namespace Modules\Communication\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Communication\Actions\CreateMessageAction;
use Modules\Communication\DTOs\CreateMessageDTO;
use Modules\Communication\Http\Requests\StoreMessageRequest;
use Modules\Communication\Models\BroadcastMessage;
use Modules\Communication\Queries\MessageQuery;
use Modules\Communication\Services\AudienceOptionsService;
use Modules\Communication\Services\RecipientResolverService;
use Modules\Communication\Transformers\MessageResource;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\Core\DTOs\ListQueryDTO;

class MessageController extends BaseInertiaController
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', BroadcastMessage::class);

        $filters = ListQueryDTO::fromRequest($request);

        $query = new MessageQuery;

        $messages = $query
            ->search($filters->search)
            ->where($filters->values)
            ->sort($filters->sortBy, $filters->sortOrder)
            ->paginate($filters->perPage);

        return $this->render('Communication::Messages/Index', [
            'messages' => MessageResource::collection($messages),
            'filters' => [
                Pagination::SEARCH_PARAM => $filters->search,
                ...$query->activeFilters($filters->values),
            ],
            'filterDefinitions' => $query->filterSchema([
                'target_type' => array_map(
                    static fn (string $type): array => [
                        'title' => __("communication::communication.targets.{$type}"),
                        'value' => $type,
                    ],
                    RecipientResolverService::TYPES,
                ),
            ]),
        ]);
    }

    public function create(AudienceOptionsService $audienceOptions): Response
    {
        $this->authorize('create', BroadcastMessage::class);

        return $this->render('Communication::Messages/Create', $audienceOptions->forForm());
    }

    public function store(StoreMessageRequest $request, CreateMessageAction $action): RedirectResponse
    {
        $this->authorize('create', BroadcastMessage::class);

        $action->execute(CreateMessageDTO::fromRequest($request));

        return $this->toRoute('communication.messages.index', [], __('communication::communication.message_sent'));
    }

    public function show(BroadcastMessage $message): Response
    {
        $this->authorize('view', $message);

        $message->load(['sender']);

        return $this->render('Communication::Messages/Show', [
            'message' => new MessageResource($message),
        ]);
    }
}
