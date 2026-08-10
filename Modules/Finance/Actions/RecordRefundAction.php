<?php

namespace Modules\Finance\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Finance\DTOs\CreateRefundDTO;
use Modules\Finance\Enums\PaymentStatus;
use Modules\Finance\Exceptions\FinanceOperationException;
use Modules\Finance\Models\Payment;
use Modules\Finance\Models\Refund;
use Modules\Finance\Repositories\InvoiceRepositoryInterface;

/**
 * Records money the school has decided to return.
 *
 * The application never moves funds — the transfer happens at the bank or the
 * front desk. What this guarantees is that the recorded total can never exceed
 * what was actually paid, and that the payment's status reflects how much of
 * it has been given back.
 */
class RecordRefundAction extends BaseAction
{
    public function __construct(
        protected InvoiceRepositoryInterface $invoices,
    ) {}

    /**
     * @param  CreateRefundDTO  $input
     */
    public function execute(mixed $input): Refund
    {
        /** @var CreateRefundDTO $dto */
        $dto = $input;

        return $this->executeWithTransaction(function () use ($dto): Refund {
            if ($dto->amount <= 0) {
                throw FinanceOperationException::refundAmountMustBePositive();
            }

            /*
             * The lock is what makes the ceiling hold. Two admins each
             * refunding 4,000 against a 5,000 payment would both read a 5,000
             * headroom without it; serialising the read means the second sees
             * the first refund and is rejected.
             */
            $payment = Payment::query()
                ->whereKey($dto->paymentId)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $payment->status->isRefundable()) {
                throw FinanceOperationException::paymentNotRefundable();
            }

            $refundable = $payment->refundableAmount();

            if (round($dto->amount, 2) > $refundable) {
                throw FinanceOperationException::refundExceedsPaidAmount();
            }

            $refund = Refund::query()->create([
                ...$dto->toArray(),
                'student_id' => $payment->student_id,
                'created_by' => auth()->id(),
            ]);

            $refundedTotal = round((float) $payment->refunded_amount + $dto->amount, 2);

            $payment->update([
                'refunded_amount' => $refundedTotal,
                'status' => $refundedTotal >= round((float) $payment->amount, 2)
                    ? PaymentStatus::Refunded
                    : PaymentStatus::PartiallyRefunded,
            ]);

            /*
             * A refunded payment still counts as settled, so the invoice's
             * paid total is unchanged; syncing keeps its status consistent
             * with the payment rows behind it.
             */
            $this->invoices->syncTotals($this->invoices->findByIdOrFail($payment->invoice_id));

            return $refund->load(['payment', 'student.user']);
        });
    }
}
