<?php

namespace Modules\Subject\Transformers;

use Modules\Core\Abstracts\BaseResource;

class SubjectResource extends BaseResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'name_translations' => $this->getTranslations('name'),
            'description' => $this->description,
            'description_translations' => $this->getTranslations('description'),
            'short_name' => $this->short_name,
            'subject_type' => $this->subject_type,
            'pass_grade' => (float) $this->pass_grade,
            'credit_hours' => $this->credit_hours,
            'max_marks' => (float) $this->max_marks,
            'color' => $this->color,
            'icon' => $this->icon,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}
