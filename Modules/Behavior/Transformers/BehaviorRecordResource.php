<?php

namespace Modules\Behavior\Transformers;

use Illuminate\Http\Request;
use Modules\Behavior\Models\BehaviorRecord;
use Modules\Core\Abstracts\BaseResource;

/**
 * @mixin BehaviorRecord
 */
class BehaviorRecordResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'behavior_category_id' => $this->behavior_category_id,
            'date' => $this->date?->toDateString(),
            'points' => $this->points,
            'description' => $this->description,
            'status' => $this->status,
            'reported_by' => $this->reported_by,
            'reviewed_by' => $this->reviewed_by,
            'created_at' => $this->created_at?->toIso8601String(),

            'student' => $this->whenLoaded('student', fn () => [
                'id' => $this->student->id,
                'name' => $this->student->relationLoaded('user') ? $this->student->user?->name : null,
            ]),

            'behavior_category' => $this->whenLoaded('behaviorCategory', fn () => [
                'id' => $this->behaviorCategory->id,
                'name' => $this->behaviorCategory->name,
                'type' => $this->behaviorCategory->type,
                'points' => $this->behaviorCategory->points,
            ]),

            'reported_by_user' => $this->whenLoaded('reportedBy', fn () => $this->reportedBy ? [
                'id' => $this->reportedBy->id,
                'name' => $this->reportedBy->name,
            ] : null),

            'reviewed_by_user' => $this->whenLoaded('reviewedBy', fn () => $this->reviewedBy ? [
                'id' => $this->reviewedBy->id,
                'name' => $this->reviewedBy->name,
            ] : null),
        ];
    }
}
