<?php

namespace Modules\Finance\Repositories;

use Modules\Finance\DTOs\CreatePaymentDTO;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Models\Payment;

interface PaymentRepositoryInterface
{
    public function findById(int $id): ?Payment;

    public function findByIdOrFail(int $id): Payment;

    public function create(CreatePaymentDTO $dto, Invoice $invoice, ?int $actorId = null): Payment;

    public function delete(Payment $payment): bool;

    public function restore(Payment $payment): bool;

    /** Total already settled against an invoice, locked against concurrent writes. */
    public function lockPaidTotalForInvoice(int $invoiceId): float;
}
