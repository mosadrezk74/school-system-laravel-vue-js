<?php

namespace Modules\Finance\Repositories;

use Modules\Finance\DTOs\CreateInvoiceDTO;
use Modules\Finance\DTOs\UpdateInvoiceDTO;
use Modules\Finance\Models\Invoice;

interface InvoiceRepositoryInterface
{
    public function findById(int $id): ?Invoice;

    public function findByIdOrFail(int $id): Invoice;

    public function existsByNumber(string $invoiceNumber, ?int $excludeId = null): bool;

    public function create(CreateInvoiceDTO $dto, string $invoiceNumber, ?int $actorId = null): Invoice;

    public function update(Invoice $invoice, UpdateInvoiceDTO $dto): Invoice;

    public function delete(Invoice $invoice): bool;

    public function restore(Invoice $invoice): bool;

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function replaceItems(Invoice $invoice, array $items): Invoice;

    public function syncTotals(Invoice $invoice): Invoice;

    /**
     * Highest numeric sequence already used for this year's auto-generated
     * invoice numbers, locked so a concurrent caller blocks until commit.
     */
    public function lockNextNumberSequence(string $prefix): int;
}
