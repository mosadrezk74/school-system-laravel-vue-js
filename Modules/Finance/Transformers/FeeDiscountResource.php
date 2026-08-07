<?php

namespace Modules\Finance\Transformers;

use Illuminate\Http\Request;
use Modules\Core\Abstracts\BaseResource;
use Modules\Finance\Models\FeeDiscount;

/**
 * @mixin FeeDiscount
 */
class FeeDiscountResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'discount_type' => $this->discount_type?->value,
            'discount_type_label' => $this->discount_type?->label(),
            'discount_value' => $this->discount_value,
            'max_amount' => $this->max_amount,
            'grade_level_id' => $this->grade_level_id,
            'classroom_id' => $this->classroom_id,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'usage_limit' => $this->usage_limit,
            'used_count' => $this->used_count,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),

            'grade_level' => $this->whenLoaded('gradeLevel', fn () => $this->gradeLevel ? [
                'id' => $this->gradeLevel->id,
                'name' => $this->gradeLevel->name,
            ] : null),

            'classroom' => $this->whenLoaded('classroom', fn () => $this->classroom ? [
                'id' => $this->classroom->id,
                'name' => $this->classroom->name,
            ] : null),
        ];
    }
}
