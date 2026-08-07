<?php

namespace Modules\Finance\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Repositories\InvoiceRepositoryInterface;

class ArchiveInvoiceAction extends BaseAction
{
    public function __construct(
        protected InvoiceRepositoryInterface $repository,
    ) {}

    /**
     * @param  Invoice  $input
     */
    public function execute(mixed $input): bool
    {
        /** @var Invoice $invoice */
        $invoice = $input;

        return $this->executeWithTransaction(fn (): bool => $this->repository->delete($invoice));
    }
}
