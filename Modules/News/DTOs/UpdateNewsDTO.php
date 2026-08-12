<?php

namespace Modules\News\DTOs;

use Illuminate\Http\Request;
use Modules\News\Enums\NewsAudience;
use Modules\News\Enums\NewsStatus;

final readonly class UpdateNewsDTO
{
    /**
     * @param  array<string, string|null>  $title
     * @param  array<string, string|null>  $body
     */
    public function __construct(
        public array $title,
        public array $body,
        public NewsAudience $audience,
        public NewsStatus $status,
        public ?string $publishedAt,
        public bool $isPinned,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            title: $request->validated('title'),
            body: $request->validated('body'),
            audience: NewsAudience::from((string) $request->validated('audience')),
            status: NewsStatus::from((string) $request->validated('status')),
            publishedAt: $request->validated('published_at'),
            isPinned: (bool) $request->validated('is_pinned', false),
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
            'audience' => $this->audience,
            'status' => $this->status,
            'published_at' => $this->publishedAt,
            'is_pinned' => $this->isPinned,
        ];
    }
}
