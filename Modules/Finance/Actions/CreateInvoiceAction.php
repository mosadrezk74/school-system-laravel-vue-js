<?php

namespace Modules\Finance\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Finance\DTOs\CreateInvoiceDTO;
use Modules\Finance\Exceptions\FinanceOperationException;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Repositories\InvoiceRepositoryInterface;

class CreateInvoiceAction extends BaseAction
{
    public function __construct(
        protected InvoiceRepositoryInterface $repository,
    ) {}

    /**
     * @param  CreateInvoiceDTO  $input
     */
    public function execute(mixed $input): Invoice
    {
        /** @var CreateInvoiceDTO $dto */
        $dto = $input;

        return $this->executeWithTransaction(function () use ($dto): Invoice {
            if ($dto->items === []) {
                throw FinanceOperationException::invoiceHasNoItems();
            }

            $invoiceNumber = $dto->invoiceNumber;

            if ($invoiceNumber) {
                if ($this->repository->existsByNumber($invoiceNumber)) {
                    throw FinanceOperationException::invoiceNumberAlreadyExists();
                }
            } else {
                $invoiceNumber = $this->generateNumber();
            }

            $invoice = $this->repository->create($dto, $invoiceNumber, auth()->id());

            $this->repository->replaceItems($invoice, $dto->items);

            return $this->repository->syncTotals($invoice)
                ->load(['student.user', 'items.feeStructure', 'payments']);
        });
    }

    private function generateNumber(): string
    {
        $prefix = config('finance.invoice_number_prefix', 'INV').'-'.now()->year;

        $sequence = $this->repository->lockNextNumberSequence($prefix) + 1;

        return sprintf('%s-%05d', $prefix, $sequence);
    }
}
