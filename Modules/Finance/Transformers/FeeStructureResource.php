<?php

namespace Modules\Finance\Transformers;

use Illuminate\Http\Request;
use Modules\Core\Abstracts\BaseResource;
use Modules\Finance\Models\FeeStructure;

/**
 * @mixin FeeStructure
 */
class FeeStructureResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'fee_category_id' => $this->fee_category_id,
            'grade_level_id' => $this->grade_level_id,
            'classroom_id' => $this->classroom_id,
            'name' => $this->name,
            'amount' => $this->amount,
            'frequency' => $this->frequency?->value,
            'frequency_label' => $this->frequency?->label(),
            'is_optional' => (bool) $this->is_optional,
            'due_day' => $this->due_day,
            'late_fee_amount' => $this->late_fee_amount,
            'late_fee_days' => $this->late_fee_days,
            'created_at' => $this->created_at?->toIso8601String(),

            'fee_category' => $this->whenLoaded('feeCategory', fn () => $this->feeCategory ? [
                'id' => $this->feeCategory->id,
                'name' => $this->feeCategory->name,
            ] : null),

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
