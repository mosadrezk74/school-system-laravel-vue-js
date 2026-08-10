<?php

namespace Modules\Finance\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Finance\Enums\PaymentStatus;
use Modules\Finance\Exceptions\FinanceOperationException;
use Modules\Finance\Models\Payment;
use Modules\Finance\Repositories\InvoiceRepositoryInterface;
use Modules\Finance\Repositories\PaymentRepositoryInterface;

/**
 * Marks a pending request as money actually received. Admin-only: this is the
 * step that turns a parent's intent into a settled payment, and it is the
 * reason a parent can never mark their own cash payment paid.
 */
class ConfirmPaymentAction extends BaseAction
{
    public function __construct(
        protected PaymentRepositoryInterface $payments,
        protected InvoiceRepositoryInterface $invoices,
    ) {}

    /**
     * @param  Payment  $input
     */
    public function execute(mixed $input): Payment
    {
        /** @var Payment $payment */
        $payment = $input;

        return $this->executeWithTransaction(function () use ($payment): Payment {
            /*
             * Re-read under a lock: two admins clicking confirm on the same
             * request must not both push the amount onto the invoice.
             */
            $locked = Payment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if (! $locked->status->isAwaitingConfirmation()) {
                throw FinanceOperationException::paymentNotAwaitingConfirmation();
            }

            $invoice = $this->invoices->findByIdOrFail($locked->invoice_id);

            /*
             * The balance may have moved since the parent opened the request
             * (another instalment, a corrected invoice), so the overpayment
             * guard runs again at confirmation time rather than only up front.
             */
            $alreadyPaid = $this->payments->lockPaidTotalForInvoice($invoice->id);

            if (round($alreadyPaid + (float) $locked->amount, 2) > round((float) $invoice->total_amount, 2)) {
                throw FinanceOperationException::paymentExceedsBalance();
            }

            $locked->update([
                'status' => PaymentStatus::Paid,
                'paid_at' => $locked->paid_at ?? now(),
                'confirmed_at' => now(),
                'confirmed_by' => auth()->id(),
            ]);

            $this->invoices->syncTotals($invoice);

            return $locked->refresh()->load(['student.user', 'invoice', 'paymentMethod']);
        });
    }
}
