<?php

namespace Modules\Communication\Transformers;

use Illuminate\Http\Request;
use Modules\Communication\Models\BroadcastMessage;
use Modules\Core\Abstracts\BaseResource;

/**
 * @mixin BroadcastMessage
 */
class MessageResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'title_translations' => $this->getTranslations('title'),
            'body' => $this->body,
            'body_translations' => $this->getTranslations('body'),
            'target_type' => $this->target_type,
            'target_ids' => $this->target_ids,
            'status' => $this->status,
            'sent_at' => $this->sent_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),

            'sender' => $this->whenLoaded('sender', fn () => $this->sender ? [
                'id' => $this->sender->id,
                'name' => $this->sender->name,
            ] : null),
        ];
    }
}
