<?php

namespace Modules\Setting\DTOs;

use Illuminate\Http\Request;

final readonly class UpdateSchoolTermDTO
{
    /**
     * @param  array<string, string|null>  $title
     * @param  array<string, string|null>  $content
     */
    public function __construct(
        public array $title,
        public array $content,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            title: $request->validated('title'),
            content: $request->validated('content'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
        ];
    }
}
