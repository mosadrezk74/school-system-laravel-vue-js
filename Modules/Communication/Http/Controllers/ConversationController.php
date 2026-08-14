<?php

namespace Modules\Communication\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Communication\Actions\StartConversationAction;
use Modules\Communication\DTOs\StartConversationDTO;
use Modules\Communication\Enums\ConversationType;
use Modules\Communication\Http\Requests\StoreConversationRequest;
use Modules\Communication\Models\Conversation;
use Modules\Communication\Queries\ConversationMessageQuery;
use Modules\Communication\Queries\ConversationQuery;
use Modules\Communication\Rules\MessageAttachmentRule;
use Modules\Communication\Services\ConversationEligibilityService;
use Modules\Communication\Transformers\ConversationMessageResource;
use Modules\Communication\Transformers\ConversationParticipantResource;
use Modules\Communication\Transformers\ConversationResource;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;

/**
 * The chat screen: a list of the signed-in user's conversations, and — on
 * `show` — the messages of the one they opened.
 *
 * Both pages render the same sidebar, so `show` loads the list too. Nothing
 * here reads a conversation id out of the query string: the route binding
 * supplies the model and the policy decides whether this user is a member of
 * it, which is what makes an id belonging to someone else's thread a 403
 * rather than a read.
 */
class ConversationController extends BaseInertiaController
{
    /** One screenful of history plus headroom, per infinite-scroll page. */
    private const MESSAGES_PER_PAGE = 30;

    public function __construct(
        private readonly ConversationEligibilityService $eligibility,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Conversation::class);

        return $this->render('Communication::Chat/Index', [
            ...$this->listProps($request),
            ...$this->composerProps($request),
        ]);
    }

    public function show(Request $request, Conversation $conversation): Response
    {
        $this->authorize('view', $conversation);

        $conversation->load(['activeParticipants.user:id,name,role']);

        $messageQuery = new ConversationMessageQuery($conversation);

        return $this->render('Communication::Chat/Show', [
            ...$this->listProps($request),
            ...$this->composerProps($request),

            'conversation' => new ConversationResource($conversation),
            'participants' => ConversationParticipantResource::collection($conversation->activeParticipants),

            /*
             * Newest-first so page 1 is the bottom of the thread; the page
             * renders it reversed and asks for older pages as the user scrolls
             * up. Inertia::scroll() is what tells the client to append rather
             * than replace, so history accumulates instead of flickering.
             */
            'messages' => Inertia::scroll(fn () => ConversationMessageResource::collection(
                (new ConversationMessageQuery($conversation))
                    ->search($request->string('message_search')->toString() ?: null)
                    ->where($request->all())
                    ->sort('sent_at', 'desc')
                    ->paginate($request->integer(Pagination::PER_PAGE_PARAM, self::MESSAGES_PER_PAGE)),
            )),

            'messageFilters' => [
                'message_search' => $request->string('message_search')->toString() ?: null,
                ...$messageQuery->activeFilters($request->all()),
            ],
            'messageFilterDefinitions' => $messageQuery->filterSchema(),
        ]);
    }

    public function store(StoreConversationRequest $request, StartConversationAction $action): RedirectResponse
    {
        $this->authorize('create', Conversation::class);

        $conversation = $action->execute(StartConversationDTO::fromRequest($request));

        return $this->toRoute(
            'communication.chat.show',
            ['conversation' => $conversation],
            __('communication::chat.started'),
        );
    }

    /**
     * The conversation sidebar, shared by both pages.
     *
     * @return array<string, mixed>
     */
    private function listProps(Request $request): array
    {
        $query = new ConversationQuery((int) $request->user()->id);

        $conversations = $query
            ->search($request->string(Pagination::SEARCH_PARAM)->toString() ?: null)
            ->where($request->all())
            ->sort($request->string(Pagination::SORT_PARAM, 'last_message_at')->toString(), $request->string(Pagination::ORDER_PARAM, 'desc')->toString())
            ->paginate($request->integer(Pagination::PER_PAGE_PARAM, Pagination::DEFAULT_PER_PAGE));

        return [
            'conversations' => ConversationResource::collection($conversations),
            'filters' => [
                Pagination::SEARCH_PARAM => $request->string(Pagination::SEARCH_PARAM)->toString() ?: null,
                ...$query->activeFilters($request->all()),
            ],
            'filterDefinitions' => $query->filterSchema(),
        ];
    }

    /**
     * Props the "new conversation" dialog needs.
     *
     * The directory is optional: it is only resolved when the page asks for it
     * by name, so opening the chat does not pay for a user lookup the reader
     * may never use — and the search term is applied server-side, so the
     * client never receives a list it was not entitled to.
     *
     * @return array<string, mixed>
     */
    private function composerProps(Request $request): array
    {
        return [
            'directory' => Inertia::optional(fn () => $this->eligibility
                ->directory($request->string('directory_search')->toString() ?: null)
                ->map(fn (User $user): array => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role' => $user->role?->value,
                ])
                ->values()),

            /* The enum is the option list; no Vue file restates the domain. */
            'conversationTypes' => ConversationType::selectOptions(),

            'attachmentRules' => [
                'max_files' => MessageAttachmentRule::maxPerMessage(),
                'max_size_kb' => MessageAttachmentRule::maxSizeKb(),
                'extensions' => MessageAttachmentRule::allowedExtensions(),
                'accept' => MessageAttachmentRule::acceptAttribute(),
            ],
        ];
    }
}
