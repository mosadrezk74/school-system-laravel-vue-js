<?php

namespace Modules\Finance\Repositories;

use Modules\Finance\DTOs\CreatePaymentDTO;
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
        return Payment::query()->create([
            ...$dto->toArray(),
            'student_id' => $invoice->student_id,
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

        return (float) Payment::query()->where('invoice_id', $invoiceId)->sum('amount');
    }
}
