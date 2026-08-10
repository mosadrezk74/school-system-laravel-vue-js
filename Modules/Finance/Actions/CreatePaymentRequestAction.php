<?php

namespace Modules\Finance\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Finance\DTOs\CreatePaymentRequestDTO;
use Modules\Finance\Enums\PaymentStatus;
use Modules\Finance\Exceptions\FinanceOperationException;
use Modules\Finance\Models\Payment;
use Modules\Finance\Models\PaymentMethod;
use Modules\Finance\Repositories\InvoiceRepositoryInterface;

/**
 * Opens a parent-initiated payment against an invoice.
 *
 * The result is always `pending`. Following a payment link or picking "cash"
 * is a statement of intent, not evidence that money arrived, so nothing here
 * settles an invoice — only an admin confirmation does.
 */
class CreatePaymentRequestAction extends BaseAction
{
    public function __construct(
        protected InvoiceRepositoryInterface $invoices,
    ) {}

    /**
     * @param  CreatePaymentRequestDTO  $input
     */
    public function execute(mixed $input): Payment
    {
        /** @var CreatePaymentRequestDTO $dto */
        $dto = $input;

        return $this->executeWithTransaction(function () use ($dto): Payment {
            /*
             * Re-read the method from the database instead of trusting the id:
             * a disabled method must not become payable by posting its id.
             */
            $method = PaymentMethod::query()
                ->active()
                ->find($dto->paymentMethodId);

            if (! $method) {
                throw FinanceOperationException::paymentMethodNotAvailable();
            }

            $invoice = $this->invoices->findByIdOrFail($dto->invoiceId);

            if ($invoice->status->isSettled()) {
                throw FinanceOperationException::invoiceAlreadySettled();
            }

            /*
             * The amount is the invoice's own outstanding balance, computed
             * server-side. Any figure the form carried is ignored.
             */
            $amount = round((float) $invoice->balance_due, 2);

            if ($amount <= 0) {
                throw FinanceOperationException::paymentAmountMustBePositive();
            }

            $payment = Payment::query()->create([
                'invoice_id' => $invoice->id,
                'student_id' => $invoice->student_id,
                'amount' => $amount,
                'payment_method' => null,
                'payment_method_id' => $method->id,
                'status' => PaymentStatus::Pending,
                'paid_at' => null,
                'notes' => $dto->notes,
                'requested_by' => auth()->id(),
                'created_by' => auth()->id(),
            ]);

            return $payment->load(['student.user', 'invoice', 'paymentMethod']);
        });
    }
}
