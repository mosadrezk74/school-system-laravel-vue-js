<?php

namespace Modules\Academic\Transformers;

use Illuminate\Http\Request;
use Modules\Academic\Models\GradeLevel;
use Modules\Core\Abstracts\BaseResource;

/**
 * @mixin GradeLevel
 */
class GradeLevelResource extends BaseResource
{
    /**
     * `name`/`description` resolve to the active locale for display, while the
     * *_translations arrays carry every stored locale for the Show page.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_translations' => $this->getTranslations('name'),
            'description' => $this->description,
            'description_translations' => $this->getTranslations('description'),
            'display_order' => $this->display_order,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
