<?php

namespace Modules\Communication\DTOs;

use Modules\Communication\Http\Requests\StoreMessageRequest;

final readonly class CreateMessageDTO
{
    public function __construct(
        public array $title,
        public array $body,
        public string $targetType,
        public ?array $targetIds,
    ) {}

    public static function fromRequest(StoreMessageRequest $request): self
    {
        return new self(
            title: $request->validated('title'),
            body: $request->validated('body'),
            targetType: $request->validated('target_type'),
            targetIds: $request->validated('target_ids'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'target_type' => $this->targetType,
            'target_ids' => $this->targetIds,
        ];
    }
}
