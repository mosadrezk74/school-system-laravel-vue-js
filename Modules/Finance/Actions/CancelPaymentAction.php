<?php

namespace Modules\Finance\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Finance\Enums\PaymentStatus;
use Modules\Finance\Exceptions\FinanceOperationException;
use Modules\Finance\Models\Payment;
use Modules\Finance\Repositories\InvoiceRepositoryInterface;

/**
 * Closes a request that will not be honoured — a parent who changed their
 * mind, or a transfer that never arrived. Only pending requests can be
 * cancelled; settled money is reversed with a refund, not a cancellation.
 */
class CancelPaymentAction extends BaseAction
{
    public function __construct(
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
            $locked = Payment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if (! $locked->status->isAwaitingConfirmation()) {
                throw FinanceOperationException::paymentNotAwaitingConfirmation();
            }

            $locked->update(['status' => PaymentStatus::Cancelled]);

            $this->invoices->syncTotals($this->invoices->findByIdOrFail($locked->invoice_id));

            return $locked->refresh();
        });
    }
}
