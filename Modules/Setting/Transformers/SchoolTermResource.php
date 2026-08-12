<?php

namespace Modules\Setting\Transformers;

use Illuminate\Http\Request;
use Modules\Core\Abstracts\BaseResource;
use Modules\Setting\Models\SchoolTerm;

/**
 * @mixin SchoolTerm
 */
class SchoolTermResource extends BaseResource
{
    /**
     * `title`/`content` resolve to the active locale for display, while the
     * *_translations arrays carry every stored locale for the edit form.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'version' => $this->version,

            'title' => $this->title,
            'title_translations' => $this->getTranslations('title'),
            'content' => $this->content,
            'content_translations' => $this->getTranslations('content'),

            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'status_color' => $this->status->color(),

            // Drives which actions the row offers; the policy is still what
            // decides whether the request is allowed through.
            'is_editable' => $this->status->isEditable(),

            'published_at' => $this->published_at?->toIso8601String(),
            'created_by' => $this->whenLoaded('createdBy', fn () => $this->createdBy?->name),
            'acceptances_count' => $this->whenCounted('acceptances'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
