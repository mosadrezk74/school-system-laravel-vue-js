<?php

namespace Modules\Finance\Transformers;

use Illuminate\Http\Request;
use Modules\Core\Abstracts\BaseResource;
use Modules\Finance\Models\Payment;

/**
 * @mixin Payment
 */
class PaymentResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_id' => $this->invoice_id,
            'student_id' => $this->student_id,
            'amount' => $this->amount,
            'refunded_amount' => $this->refunded_amount,
            'refundable_amount' => $this->refundableAmount(),
            'payment_method' => $this->payment_method?->value,
            'payment_method_label' => $this->payment_method?->label(),
            'payment_method_id' => $this->payment_method_id,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'is_refundable' => (bool) $this->status?->isRefundable(),
            'awaiting_confirmation' => (bool) $this->status?->isAwaitingConfirmation(),
            'reference_number' => $this->reference_number,
            'paid_at' => $this->paid_at?->toIso8601String(),
            'confirmed_at' => $this->confirmed_at?->toIso8601String(),
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),

            /*
             * The configured method, including the URL and bank details the
             * parent needs to act on a pending request.
             */
            'payment_method_details' => $this->whenLoaded('paymentMethod', fn () => $this->paymentMethod ? [
                'id' => $this->paymentMethod->id,
                'name' => $this->paymentMethod->name,
                'type' => $this->paymentMethod->type?->value,
                'instructions' => $this->paymentMethod->instructions,
                'payment_url' => $this->paymentMethod->payment_url,
                'bank_name' => $this->paymentMethod->bank_name,
                'account_name' => $this->paymentMethod->account_name,
                'account_number' => $this->paymentMethod->account_number,
                'iban' => $this->paymentMethod->iban,
            ] : null),

            'student' => $this->whenLoaded('student', fn () => $this->student ? [
                'id' => $this->student->id,
                'name' => $this->student->user?->name,
                'admission_number' => $this->student->admission_number,
            ] : null),

            'invoice' => $this->whenLoaded('invoice', fn () => $this->invoice ? [
                'id' => $this->invoice->id,
                'invoice_number' => $this->invoice->invoice_number,
                'total_amount' => $this->invoice->total_amount,
                'balance_due' => $this->invoice->balance_due,
            ] : null),
        ];
    }
}
