<?php

namespace Modules\Finance\Transformers;

use Illuminate\Http\Request;
use Modules\Core\Abstracts\BaseResource;
use Modules\Finance\Models\StudentFee;

/**
 * @mixin StudentFee
 */
class StudentFeeResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'fee_structure_id' => $this->fee_structure_id,
            'amount' => $this->amount,
            'discount_amount' => $this->discount_amount,
            'final_amount' => $this->final_amount,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),

            'student' => $this->whenLoaded('student', fn () => $this->student ? [
                'id' => $this->student->id,
                'name' => $this->student->user?->name,
                'admission_number' => $this->student->admission_number,
            ] : null),

            'fee_structure' => $this->whenLoaded('feeStructure', fn () => $this->feeStructure ? [
                'id' => $this->feeStructure->id,
                'name' => $this->feeStructure->name,
                'fee_category' => $this->feeStructure->relationLoaded('feeCategory') ? [
                    'id' => $this->feeStructure->feeCategory?->id,
                    'name' => $this->feeStructure->feeCategory?->name,
                ] : null,
            ] : null),
        ];
    }
}
