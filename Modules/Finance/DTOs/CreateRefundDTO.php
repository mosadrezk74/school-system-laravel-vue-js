<?php

namespace Modules\Finance\DTOs;

use Modules\Finance\Http\Requests\StoreRefundRequest;

final readonly class CreateRefundDTO
{
    public function __construct(
        public int $paymentId,
        public float $amount,
        public string $refundDate,
        public ?string $refundMethod,
        public ?string $reason,
        public ?string $notes,
    ) {}

    public static function fromRequest(StoreRefundRequest $request): self
    {
        return new self(
            paymentId: (int) $request->validated('payment_id'),
            amount: (float) $request->validated('amount'),
            refundDate: $request->validated('refund_date'),
            refundMethod: $request->validated('refund_method') ?: null,
            reason: $request->validated('reason') ?: null,
            notes: $request->validated('notes') ?: null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'payment_id' => $this->paymentId,
            'amount' => $this->amount,
            'refund_date' => $this->refundDate,
            'refund_method' => $this->refundMethod,
            'reason' => $this->reason,
            'notes' => $this->notes,
        ];
    }
}
