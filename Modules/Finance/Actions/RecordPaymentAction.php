<?php

namespace Modules\Finance\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Finance\DTOs\CreatePaymentDTO;
use Modules\Finance\Enums\InvoiceStatus;
use Modules\Finance\Exceptions\FinanceOperationException;
use Modules\Finance\Models\Payment;
use Modules\Finance\Repositories\InvoiceRepositoryInterface;
use Modules\Finance\Repositories\PaymentRepositoryInterface;

class RecordPaymentAction extends BaseAction
{
    public function __construct(
        protected PaymentRepositoryInterface $payments,
        protected InvoiceRepositoryInterface $invoices,
    ) {}

    /**
     * @param  CreatePaymentDTO  $input
     */
    public function execute(mixed $input): Payment
    {
        /** @var CreatePaymentDTO $dto */
        $dto = $input;

        return $this->executeWithTransaction(function () use ($dto): Payment {
            if ($dto->amount <= 0) {
                throw FinanceOperationException::paymentAmountMustBePositive();
            }

            $invoice = $this->invoices->findByIdOrFail($dto->invoiceId);

            if ($invoice->status === InvoiceStatus::Cancelled) {
                throw FinanceOperationException::invoiceNotEditable();
            }

            /*
             * Takes a row lock on the invoice before summing what is already
             * settled, so two cashiers taking the last instalment at the same
             * moment cannot both read the old balance and overpay it.
             */
            $alreadyPaid = $this->payments->lockPaidTotalForInvoice($invoice->id);

            if (round($alreadyPaid + $dto->amount, 2) > round((float) $invoice->total_amount, 2)) {
                throw FinanceOperationException::paymentExceedsBalance();
            }

            $payment = $this->payments->create($dto, $invoice, auth()->id());

            $this->invoices->syncTotals($invoice);

            return $payment->load(['student.user', 'invoice']);
        });
    }
}
