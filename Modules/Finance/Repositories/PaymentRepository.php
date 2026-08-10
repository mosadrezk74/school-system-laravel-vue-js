<?php

namespace Modules\Finance\Repositories;

use Modules\Finance\DTOs\CreatePaymentDTO;
use Modules\Finance\Enums\PaymentStatus;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Models\Payment;

class PaymentRepository implements PaymentRepositoryInterface
{
    public function findById(int $id): ?Payment
    {
        return Payment::query()->find($id);
    }

    public function findByIdOrFail(int $id): Payment
    {
        return Payment::query()->findOrFail($id);
    }

    public function create(CreatePaymentDTO $dto, Invoice $invoice, ?int $actorId = null): Payment
    {
        /*
         * An admin recording a payment is recording money already received,
         * which is what this table meant before parent requests existed. It
         * lands settled and confirmed so that behaviour is unchanged.
         */
        return Payment::query()->create([
            ...$dto->toArray(),
            'student_id' => $invoice->student_id,
            'status' => PaymentStatus::Paid,
            'confirmed_at' => now(),
            'confirmed_by' => $actorId,
            'created_by' => $actorId,
        ]);
    }

    public function delete(Payment $payment): bool
    {
        return (bool) $payment->delete();
    }

    public function restore(Payment $payment): bool
    {
        return (bool) $payment->restore();
    }

    public function lockPaidTotalForInvoice(int $invoiceId): float
    {
        Invoice::query()->whereKey($invoiceId)->lockForUpdate()->firstOrFail();

        /*
         * Only settled payments count. A parent's pending request sits on the
         * same table, and counting it here would let an unpaid request eat the
         * invoice balance and block the real payment that follows it.
         */
        return (float) Payment::query()
            ->where('invoice_id', $invoiceId)
            ->settled()
            ->sum('amount');
    }
}
