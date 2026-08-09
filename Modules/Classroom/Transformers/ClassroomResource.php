<?php

namespace Modules\Classroom\Transformers;

use Modules\Core\Abstracts\BaseResource;

class ClassroomResource extends BaseResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_translations' => $this->getTranslations('name'),
            'capacity' => $this->capacity,
            'floor' => $this->floor,
            'building' => $this->building,
            'room_type' => $this->room_type,
            'has_projector' => (bool) $this->has_projector,
            'has_smartboard' => (bool) $this->has_smartboard,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}
