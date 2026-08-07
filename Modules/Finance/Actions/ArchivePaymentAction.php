<?php

namespace Modules\Finance\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Finance\Models\Payment;
use Modules\Finance\Repositories\InvoiceRepositoryInterface;
use Modules\Finance\Repositories\PaymentRepositoryInterface;

class ArchivePaymentAction extends BaseAction
{
    public function __construct(
        protected PaymentRepositoryInterface $payments,
        protected InvoiceRepositoryInterface $invoices,
    ) {}

    /**
     * @param  Payment  $input
     */
    public function execute(mixed $input): bool
    {
        /** @var Payment $payment */
        $payment = $input;

        return $this->executeWithTransaction(function () use ($payment): bool {
            $invoice = $payment->invoice;

            $deleted = $this->payments->delete($payment);

            if ($invoice) {
                $this->invoices->syncTotals($invoice);
            }

            return $deleted;
        });
    }
}
