<?php

namespace Modules\Core\Transformers;

use Illuminate\Http\Request;
use Modules\Core\Abstracts\BaseResource;
use Modules\Core\Models\Activity;

/**
 * @mixin Activity
 */
class ActivityResource extends BaseResource
{
    /**
     * Carries no academic_year_id or semester_id of its own: activity_log has
     * no such columns, and the current context describes the response as a
     * whole rather than any one entry. ActivityController returns it once,
     * under `context`, so a reader cannot mistake it for a per-row stamp.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'log_name' => $this->log_name,
            'description' => $this->description,
            'event' => $this->event,
            'subject_type' => $this->subject_type,
            'subject_type_short' => $this->subject_type ? class_basename($this->subject_type) : null,
            'subject_id' => $this->subject_id,
            'causer_type' => $this->causer_type,
            'causer_id' => $this->causer_id,
            'causer' => $this->whenLoaded('causer', fn () => [
                'id' => $this->causer?->id,
                'name' => $this->causer?->name,
            ]),
            'attribute_changes' => $this->attribute_changes,
            'properties' => $this->properties,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
