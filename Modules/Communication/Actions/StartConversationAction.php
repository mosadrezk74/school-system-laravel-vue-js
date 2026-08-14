<?php

namespace Modules\Communication\Actions;

use Modules\Communication\DTOs\SendMessageDTO;
use Modules\Communication\DTOs\StartConversationDTO;
use Modules\Communication\Enums\ConversationType;
use Modules\Communication\Exceptions\ConversationOperationException;
use Modules\Communication\Models\Conversation;
use Modules\Communication\Models\ConversationParticipant;
use Modules\Communication\Services\ConversationEligibilityService;
use Modules\Core\Abstracts\BaseAction;

class StartConversationAction extends BaseAction
{
    public function __construct(
        private readonly ConversationEligibilityService $eligibility,
        private readonly SendMessageAction $sendMessage,
    ) {}

    /**
     * @param  StartConversationDTO  $input
     */
    public function execute(mixed $input): Conversation
    {
        /** @var StartConversationDTO $dto */
        $dto = $input;

        $creatorId = (int) auth()->id();

        $participantIds = array_values(array_diff($dto->participantIds, [$creatorId]));

        $this->assertShape($dto, $participantIds);
        $this->eligibility->assertEligible($participantIds);

        return $this->executeWithTransaction(function () use ($dto, $participantIds, $creatorId): Conversation {
            $conversation = $dto->type->isDirect()
                ? $this->findExistingDirect($participantIds[0], $creatorId)
                : null;

            $conversation ??= $this->create($dto, $participantIds, $creatorId);

            if (filled($dto->body)) {
                $this->sendMessage->execute([
                    'conversation' => $conversation,
                    'dto' => new SendMessageDTO(body: $dto->body, attachments: []),
                ]);
            }

            return $conversation;
        });
    }

    /**
     * @param  array<int, int>  $participantIds
     *
     * @throws ConversationOperationException
     */
    private function assertShape(StartConversationDTO $dto, array $participantIds): void
    {
        if ($dto->type->isDirect() && count($participantIds) !== 1) {
            throw ConversationOperationException::directRequiresExactlyOneOther();
        }

        if ($dto->type === ConversationType::Group) {
            if ($participantIds === []) {
                throw ConversationOperationException::ineligibleParticipants();
            }

            if (blank($dto->name)) {
                throw ConversationOperationException::groupRequiresName();
            }
        }
    }

    /**
     * Reuse the thread these two already have rather than starting a second
     * one, which would split their history in half and leave unread counts
     * spread across two rows in the list.
     *
     * Matched on membership rather than on a stored key pair, because the pair
     * is not a column. "Both of them, and nobody else" is expressed as two
     * existence checks plus one non-existence check rather than as a HAVING on
     * a counted subquery — a HAVING without a GROUP BY is not portable, and
     * this form also lets the indexes on conversation_participants do the work.
     */
    private function findExistingDirect(int $otherUserId, int $creatorId): ?Conversation
    {
        return Conversation::query()
            ->where('type', ConversationType::Direct)
            ->whereHas('participants', fn ($query) => $query->where('user_id', $creatorId))
            ->whereHas('participants', fn ($query) => $query->where('user_id', $otherUserId))
            ->whereDoesntHave(
                'participants',
                fn ($query) => $query->whereNotIn('user_id', [$creatorId, $otherUserId]),
            )
            ->first();
    }

    /**
     * @param  array<int, int>  $participantIds
     */
    private function create(StartConversationDTO $dto, array $participantIds, int $creatorId): Conversation
    {
        $conversation = Conversation::query()->create([
            'type' => $dto->type,
            // A direct thread is titled by whoever you are talking to, which
            // differs per viewer, so it carries no stored name.
            'name' => $dto->type->isDirect() ? null : $dto->name,
            'created_by' => $creatorId,
        ]);

        foreach ([$creatorId, ...$participantIds] as $userId) {
            ConversationParticipant::query()->create([
                'conversation_id' => $conversation->id,
                'user_id' => $userId,
                // The creator has by definition seen everything so far.
                'last_read_at' => $userId === $creatorId ? now() : null,
                'is_active' => true,
            ]);
        }

        return $conversation;
    }
}
