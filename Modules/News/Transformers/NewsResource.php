<?php

namespace Modules\News\Transformers;

use Illuminate\Http\Request;
use Modules\Core\Abstracts\BaseResource;
use Modules\News\Models\News;

/**
 * @mixin News
 */
class NewsResource extends BaseResource
{
    /**
     * `title`/`body` resolve to the active locale for display, while the
     * *_translations arrays carry every stored locale for the edit form.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,

            'title' => $this->title,
            'title_translations' => $this->getTranslations('title'),
            'body' => $this->body,
            'body_translations' => $this->getTranslations('body'),

            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'status_color' => $this->status->color(),

            'audience' => $this->audience->value,
            'audience_label' => $this->audience->label(),

            'published_at' => $this->published_at?->toIso8601String(),
            'is_pinned' => $this->is_pinned,

            /*
             * Published but dated ahead: the admin list shows this so a
             * scheduled item is not mistaken for one that failed to appear.
             */
            'is_scheduled' => $this->status->isPublished() && ! $this->isLive(),
            'is_live' => $this->isLive(),

            'author' => $this->whenLoaded('author', fn () => $this->author?->name),
            'created_at' => $this->created_at?->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
