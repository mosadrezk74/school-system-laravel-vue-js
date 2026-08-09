<?php

namespace Modules\Subject\Transformers;

use Modules\Core\Abstracts\BaseResource;

class SubjectResource extends BaseResource
{
    /**
     * `name`/`description` resolve to the active locale for display, while the
     * *_translations arrays carry every stored locale for the Show page.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_translations' => $this->getTranslations('name'),
            'description' => $this->description,
            'description_translations' => $this->getTranslations('description'),
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}
