<?php

namespace Modules\Finance\DTOs;

use Modules\Finance\Http\Requests\StorePaymentRequestRequest;

/**
 * A parent's intent to pay an invoice. Deliberately carries no amount: the
 * sum owed is read from the invoice server-side, so the payload cannot ask to
 * settle a 5,000 bill with 1.
 */
final readonly class CreatePaymentRequestDTO
{
    public function __construct(
        public int $invoiceId,
        public int $paymentMethodId,
        public ?string $notes,
    ) {}

    public static function fromRequest(StorePaymentRequestRequest $request): self
    {
        return new self(
            invoiceId: (int) $request->validated('invoice_id'),
            paymentMethodId: (int) $request->validated('payment_method_id'),
            notes: $request->validated('notes') ?: null,
        );
    }
}
