<?php

namespace Modules\Finance\Repositories;

use Illuminate\Support\Carbon;
use Modules\Finance\DTOs\CreateInvoiceDTO;
use Modules\Finance\DTOs\UpdateInvoiceDTO;
use Modules\Finance\Enums\InvoiceStatus;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Models\InvoiceItem;

class InvoiceRepository implements InvoiceRepositoryInterface
{
    public function findById(int $id): ?Invoice
    {
        return Invoice::query()->find($id);
    }

    public function findByIdOrFail(int $id): Invoice
    {
        return Invoice::query()->findOrFail($id);
    }

    public function existsByNumber(string $invoiceNumber, ?int $excludeId = null): bool
    {
        return Invoice::query()
            ->where('invoice_number', $invoiceNumber)
            ->when($excludeId, fn ($query) => $query->whereKeyNot($excludeId))
            ->exists();
    }

    public function create(CreateInvoiceDTO $dto, string $invoiceNumber, ?int $actorId = null): Invoice
    {
        return Invoice::query()->create([
            ...$dto->toArray(),
            'invoice_number' => $invoiceNumber,
            'subtotal' => 0,
            'discount_amount' => 0,
            'total_amount' => 0,
            'paid_amount' => 0,
            'balance_due' => 0,
            'status' => InvoiceStatus::Pending->value,
            'created_by' => $actorId,
        ]);
    }

    public function update(Invoice $invoice, UpdateInvoiceDTO $dto): Invoice
    {
        $invoice->update($dto->toArray());

        return $invoice->refresh();
    }

    public function delete(Invoice $invoice): bool
    {
        return (bool) $invoice->delete();
    }

    public function restore(Invoice $invoice): bool
    {
        return (bool) $invoice->restore();
    }

    public function replaceItems(Invoice $invoice, array $items): Invoice
    {
        $invoice->items()->delete();

        foreach ($items as $item) {
            $quantity = (int) ($item['quantity'] ?? 1);
            $unitPrice = (float) $item['unit_price'];
            $discount = (float) ($item['discount_amount'] ?? 0);

            InvoiceItem::query()->create([
                'invoice_id' => $invoice->id,
                'fee_structure_id' => $item['fee_structure_id'],
                'description' => $item['description'] ?? null,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount_amount' => $discount,
                'total' => max(0, round(($quantity * $unitPrice) - $discount, 2)),
            ]);
        }

        return $invoice->refresh();
    }

    public function syncTotals(Invoice $invoice): Invoice
    {
        $items = $invoice->items()->get();

        $subtotal = round($items->sum(fn (InvoiceItem $item): float => (float) $item->quantity * (float) $item->unit_price), 2);
        $discount = round($items->sum(fn (InvoiceItem $item): float => (float) $item->discount_amount), 2);
        $total = max(0, round($subtotal - $discount, 2));

        $paid = round((float) $invoice->payments()->settled()->sum('amount'), 2);
        $balance = max(0, round($total - $paid, 2));

        $invoice->update([
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'total_amount' => $total,
            'paid_amount' => $paid,
            'balance_due' => $balance,
            'status' => $this->resolveStatus($invoice, $total, $paid, $balance),
        ]);

        return $invoice->refresh();
    }

    public function lockNextNumberSequence(string $prefix): int
    {
        return Invoice::withTrashed()
            ->where('invoice_number', 'LIKE', "{$prefix}-%")
            ->lockForUpdate()
            ->count();
    }

    private function resolveStatus(Invoice $invoice, float $total, float $paid, float $balance): string
    {
        if ($invoice->status === InvoiceStatus::Cancelled) {
            return InvoiceStatus::Cancelled->value;
        }

        if ($total > 0 && $balance <= 0) {
            return InvoiceStatus::Paid->value;
        }

        if ($paid > 0) {
            return InvoiceStatus::Partial->value;
        }

        if ($invoice->due_date instanceof Carbon && $invoice->due_date->isPast()) {
            return InvoiceStatus::Overdue->value;
        }

        return InvoiceStatus::Pending->value;
    }
}
