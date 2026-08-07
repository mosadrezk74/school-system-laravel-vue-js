<?php

namespace Modules\Finance\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Finance\DTOs\UpdateInvoiceDTO;
use Modules\Finance\Enums\InvoiceStatus;
use Modules\Finance\Exceptions\FinanceOperationException;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Repositories\InvoiceRepositoryInterface;

class UpdateInvoiceAction extends BaseAction
{
    public function __construct(
        protected InvoiceRepositoryInterface $repository,
    ) {}

    /**
     * @param  array{invoice: Invoice, dto: UpdateInvoiceDTO}  $input
     */
    public function execute(mixed $input): Invoice
    {
        /** @var Invoice $invoice */
        $invoice = $input['invoice'];

        /** @var UpdateInvoiceDTO $dto */
        $dto = $input['dto'];

        return $this->executeWithTransaction(function () use ($invoice, $dto): Invoice {
            if ($invoice->status === InvoiceStatus::Cancelled) {
                throw FinanceOperationException::invoiceNotEditable();
            }

            if ($invoice->payments()->exists()) {
                throw FinanceOperationException::invoiceNotEditable();
            }

            if ($dto->items === []) {
                throw FinanceOperationException::invoiceHasNoItems();
            }

            $invoice = $this->repository->update($invoice, $dto);

            $this->repository->replaceItems($invoice, $dto->items);

            return $this->repository->syncTotals($invoice)
                ->load(['student.user', 'items.feeStructure', 'payments']);
        });
    }
}
