<?php

namespace Modules\Finance\DTOs;

use Modules\Finance\Http\Requests\UpdateInvoiceRequest;

final readonly class UpdateInvoiceDTO
{
    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function __construct(
        public string $issueDate,
        public string $dueDate,
        public string $currency,
        public ?string $notes,
        public array $items,
    ) {}

    public static function fromRequest(UpdateInvoiceRequest $request): self
    {
        return new self(
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
            'issue_date' => $this->issueDate,
            'due_date' => $this->dueDate,
            'currency' => $this->currency,
            'notes' => $this->notes,
        ];
    }
}
