<?php

namespace Modules\Finance\Transformers;

use Illuminate\Http\Request;
use Modules\Core\Abstracts\BaseResource;
use Modules\Finance\Models\Refund;

/**
 * @mixin Refund
 */
class RefundResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payment_id' => $this->payment_id,
            'student_id' => $this->student_id,
            'amount' => $this->amount,
            'refund_date' => $this->refund_date?->toDateString(),
            'refund_method' => $this->refund_method?->value,
            'refund_method_label' => $this->refund_method?->label(),
            'reason' => $this->reason,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),

            'student' => $this->whenLoaded('student', fn () => $this->student ? [
                'id' => $this->student->id,
                'name' => $this->student->user?->name,
                'admission_number' => $this->student->admission_number,
            ] : null),

            'payment' => $this->whenLoaded('payment', fn () => $this->payment ? [
                'id' => $this->payment->id,
                'amount' => $this->payment->amount,
                'refunded_amount' => $this->payment->refunded_amount,
                'status' => $this->payment->status?->value,
                'status_label' => $this->payment->status?->label(),
            ] : null),
        ];
    }
}
