<?php

namespace Modules\Finance\DTOs;

use Modules\Finance\Enums\StudentFeeStatus;
use Modules\Finance\Http\Requests\StoreStudentFeeRequest;

final readonly class CreateStudentFeeDTO
{
    public function __construct(
        public int $studentId,
        public int $feeStructureId,
        public float $amount,
        public float $discountAmount,
        public string $status,
    ) {}

    public static function fromRequest(StoreStudentFeeRequest $request): self
    {
        return new self(
            studentId: (int) $request->validated('student_id'),
            feeStructureId: (int) $request->validated('fee_structure_id'),
            amount: (float) $request->validated('amount'),
            discountAmount: (float) $request->validated('discount_amount', 0),
            status: $request->validated('status') ?: StudentFeeStatus::Pending->value,
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
            'student_id' => $this->studentId,
            'fee_structure_id' => $this->feeStructureId,
            'amount' => $this->amount,
            'discount_amount' => $this->discountAmount,
            'final_amount' => $this->finalAmount(),
            'status' => $this->status,
        ];
    }
}
