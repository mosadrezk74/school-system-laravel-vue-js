<?php

namespace Modules\Finance\DTOs;

use Modules\Finance\Http\Requests\UpdateStudentFeeRequest;

final readonly class UpdateStudentFeeDTO
{
    public function __construct(
        public float $amount,
        public float $discountAmount,
        public string $status,
    ) {}

    public static function fromRequest(UpdateStudentFeeRequest $request): self
    {
        return new self(
            amount: (float) $request->validated('amount'),
            discountAmount: (float) $request->validated('discount_amount', 0),
            status: $request->validated('status'),
        );
    }

    public function finalAmount(): float
    {
        return max(0, round($this->amount - $this->discountAmount, 2));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'discount_amount' => $this->discountAmount,
            'final_amount' => $this->finalAmount(),
            'status' => $this->status,
        ];
    }
}
