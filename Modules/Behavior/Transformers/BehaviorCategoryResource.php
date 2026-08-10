<?php

namespace Modules\Behavior\Transformers;

use Illuminate\Http\Request;
use Modules\Behavior\Models\BehaviorCategory;
use Modules\Core\Abstracts\BaseResource;

/**
 * @mixin BehaviorCategory
 */
class BehaviorCategoryResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_translations' => $this->getTranslations('name'),
            'type' => $this->type,
            'points' => $this->points,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
