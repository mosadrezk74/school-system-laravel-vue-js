<?php

namespace Modules\Finance\DTOs;

use Modules\Finance\Http\Requests\StorePaymentRequest;

final readonly class CreatePaymentDTO
{
    public function __construct(
        public int $invoiceId,
        public float $amount,
        public string $paymentMethod,
        public ?string $referenceNumber,
        public string $paidAt,
        public ?string $notes,
    ) {}

    public static function fromRequest(StorePaymentRequest $request): self
    {
        return new self(
            invoiceId: (int) $request->validated('invoice_id'),
            amount: (float) $request->validated('amount'),
            paymentMethod: $request->validated('payment_method'),
            referenceNumber: $request->validated('reference_number') ?: null,
            paidAt: $request->validated('paid_at'),
            notes: $request->validated('notes') ?: null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'invoice_id' => $this->invoiceId,
            'amount' => $this->amount,
            'payment_method' => $this->paymentMethod,
            'reference_number' => $this->referenceNumber,
            'paid_at' => $this->paidAt,
            'notes' => $this->notes,
        ];
    }
}
