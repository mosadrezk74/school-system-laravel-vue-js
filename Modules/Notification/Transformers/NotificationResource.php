<?php

namespace Modules\Notification\Transformers;

use Illuminate\Http\Request;
use Modules\Core\Abstracts\BaseResource;
use Modules\Notification\Models\Notification;

/**
 * @mixin Notification
 */
class NotificationResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = is_array($this->data) ? $this->data : [];

        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $data['title'] ?? null,
            'body' => $data['body'] ?? null,
            'icon' => $data['icon'] ?? 'bx-bell',
            'url' => $data['url'] ?? null,
            'is_read' => $this->read_at !== null,
            'read_at' => $this->read_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
