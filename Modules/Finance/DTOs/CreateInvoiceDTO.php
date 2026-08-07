<?php

namespace Modules\Finance\DTOs;

use Modules\Finance\Http\Requests\StoreInvoiceRequest;

final readonly class CreateInvoiceDTO
{
    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function __construct(
        public int $studentId,
        public ?string $invoiceNumber,
        public string $issueDate,
        public string $dueDate,
        public string $currency,
        public ?string $notes,
        public array $items,
    ) {}

    public static function fromRequest(StoreInvoiceRequest $request): self
    {
        return new self(
            studentId: (int) $request->validated('student_id'),
            invoiceNumber: $request->validated('invoice_number') ?: null,
            issueDate: $request->validated('issue_date'),
            dueDate: $request->validated('due_date'),
            currency: $request->validated('currency') ?: config('finance.currency', 'SAR'),
            notes: $request->validated('notes') ?: null,
            items: $request->validated('items', []),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'student_id' => $this->studentId,
            'invoice_number' => $this->invoiceNumber,
            'issue_date' => $this->issueDate,
            'due_date' => $this->dueDate,
            'currency' => $this->currency,
            'notes' => $this->notes,
        ];
    }
}
