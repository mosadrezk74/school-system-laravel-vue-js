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
use Modules\Communication\Transformers\MessageResource;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;

class MessageController extends BaseInertiaController
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', BroadcastMessage::class);

        $search = $request->string(Pagination::SEARCH_PARAM)->toString() ?: null;
        $status = $request->string('status')->toString() ?: null;

        $query = (new MessageQuery)
            ->searchTerm($search)
            ->status($status)
            ->sort(Pagination::DEFAULT_SORT, Pagination::DEFAULT_ORDER);

        $messages = $query->paginate($request->integer(Pagination::PER_PAGE_PARAM, Pagination::DEFAULT_PER_PAGE));

        return $this->render('Communication::Messages/Index', [
            'messages' => MessageResource::collection($messages),
            'filters' => $request->only([Pagination::SEARCH_PARAM, 'status']),
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
