<?php

namespace Modules\Communication\DTOs;

use Illuminate\Http\UploadedFile;
use Modules\Communication\Enums\MessageType;
use Modules\Communication\Http\Requests\SendMessageRequest;

final readonly class SendMessageDTO
{
    /**
     * @param  array<int, UploadedFile>  $attachments
     */
    public function __construct(
        public ?string $body,
        public array $attachments,
    ) {}

    public static function fromRequest(SendMessageRequest $request): self
    {
        return new self(
            body: $request->validated('body'),
            attachments: array_values(array_filter(
                (array) $request->file('attachments', []),
                static fn (mixed $file): bool => $file instanceof UploadedFile,
            )),
        );
    }

    public function hasAttachments(): bool
    {
        return $this->attachments !== [];
    }

    /**
     * A message carrying files is a `file` message whether or not it also
     * carries a caption; the caption does not make it text.
     */
    public function type(): MessageType
    {
        return $this->hasAttachments() ? MessageType::File : MessageType::Text;
    }

    public function isEmpty(): bool
    {
        return ! $this->hasAttachments() && blank($this->body);
    }
}
