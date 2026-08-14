<?php

namespace Modules\Communication\DTOs;

use Modules\Communication\Enums\ConversationType;
use Modules\Communication\Http\Requests\StoreConversationRequest;

final readonly class StartConversationDTO
{
    /**
     * @param  array<int, int>  $participantIds  Excludes the creator, who is added by the Action.
     */
    public function __construct(
        public ConversationType $type,
        public ?string $name,
        public array $participantIds,
        public ?string $body,
    ) {}

    public static function fromRequest(StoreConversationRequest $request): self
    {
        return new self(
            type: ConversationType::from($request->validated('type')),
            name: $request->validated('name'),
            participantIds: array_values(array_unique(array_map(
                static fn (mixed $id): int => (int) $id,
                (array) $request->validated('participant_ids', []),
            ))),
            body: $request->validated('body'),
        );
    }
}
