<?php

namespace Modules\Finance\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Finance\Enums\InvoiceStatus;
use Modules\Finance\Exceptions\FinanceOperationException;
use Modules\Finance\Models\Invoice;

class CancelInvoiceAction extends BaseAction
{
    /**
     * @param  Invoice  $input
     */
    public function execute(mixed $input): Invoice
    {
        /** @var Invoice $invoice */
        $invoice = $input;

        return $this->executeWithTransaction(function () use ($invoice): Invoice {
            if ($invoice->status === InvoiceStatus::Cancelled) {
                throw FinanceOperationException::invoiceAlreadyCancelled();
            }

            if ($invoice->payments()->exists()) {
                throw FinanceOperationException::cannotCancelPaidInvoice();
            }

            $invoice->update(['status' => InvoiceStatus::Cancelled->value]);

            return $invoice->refresh()->load(['student.user', 'items.feeStructure']);
        });
    }
}
