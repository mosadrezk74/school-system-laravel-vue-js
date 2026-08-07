<?php

namespace Modules\Finance\Actions;

use Modules\Core\Abstracts\BaseAction;
use Modules\Finance\DTOs\CreateInvoiceDTO;
use Modules\Finance\Exceptions\FinanceOperationException;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Models\StudentFee;
use Modules\Finance\Repositories\InvoiceRepositoryInterface;
use Modules\Finance\Repositories\StudentFeeRepositoryInterface;

class GenerateInvoiceFromStudentFeesAction extends BaseAction
{
    public function __construct(
        protected StudentFeeRepositoryInterface $studentFees,
        protected InvoiceRepositoryInterface $invoices,
        protected CreateInvoiceAction $createInvoice,
    ) {}

    /**
     * @param  array{studentId: int, issueDate: string, dueDate: string, notes: ?string}  $input
     */
    public function execute(mixed $input): Invoice
    {
        $studentId = (int) $input['studentId'];

        return $this->executeWithTransaction(function () use ($input, $studentId): Invoice {
            $pending = $this->studentFees->lockPendingForStudent($studentId);

            if ($pending->isEmpty()) {
                throw FinanceOperationException::noPendingFeesForStudent();
            }

            $items = $pending->map(fn (StudentFee $fee): array => [
                'fee_structure_id' => $fee->fee_structure_id,
                'description' => $fee->feeStructure?->getTranslations('name') ?: null,
                'quantity' => 1,
                'unit_price' => (float) $fee->amount,
                'discount_amount' => (float) $fee->discount_amount,
            ])->all();

            $invoice = $this->createInvoice->execute(new CreateInvoiceDTO(
                studentId: $studentId,
                invoiceNumber: null,
                issueDate: $input['issueDate'],
                dueDate: $input['dueDate'],
                currency: config('finance.currency', 'SAR'),
                notes: $input['notes'] ?? null,
                items: $items,
            ));

            $this->studentFees->markInvoiced($pending->pluck('id')->all());

            return $invoice;
        });
    }
}
