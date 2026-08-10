<?php

namespace Modules\Finance\DTOs;

use Modules\Finance\Http\Requests\StorePaymentMethodRequest;

final readonly class CreatePaymentMethodDTO
{
    public function __construct(
        public string $code,
        public array $name,
        public ?array $description,
        public ?array $instructions,
        public string $type,
        public ?string $paymentUrl,
        public ?string $bankName,
        public ?string $accountName,
        public ?string $accountNumber,
        public ?string $iban,
        public bool $isActive,
        public int $displayOrder,
    ) {}

    public static function fromRequest(StorePaymentMethodRequest $request): self
    {
        return new self(
            code: $request->validated('code'),
            name: $request->validated('name'),
            description: $request->validated('description'),
            instructions: $request->validated('instructions'),
            type: $request->validated('type'),
            paymentUrl: $request->validated('payment_url') ?: null,
            bankName: $request->validated('bank_name') ?: null,
            accountName: $request->validated('account_name') ?: null,
            accountNumber: $request->validated('account_number') ?: null,
            iban: $request->validated('iban') ?: null,
            isActive: (bool) $request->validated('is_active', true),
            displayOrder: (int) $request->validated('display_order', 0),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'instructions' => $this->instructions,
            'type' => $this->type,
            'payment_url' => $this->paymentUrl,
            'bank_name' => $this->bankName,
            'account_name' => $this->accountName,
            'account_number' => $this->accountNumber,
            'iban' => $this->iban,
            'is_active' => $this->isActive,
            'display_order' => $this->displayOrder,
        ];
    }
}
