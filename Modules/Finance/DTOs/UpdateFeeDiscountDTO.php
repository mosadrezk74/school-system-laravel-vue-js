<?php

namespace Modules\Finance\DTOs;

use Modules\Finance\Http\Requests\UpdateFeeDiscountRequest;

final readonly class UpdateFeeDiscountDTO
{
    public function __construct(
        public array $name,
        public ?string $code,
        public string $discountType,
        public float $discountValue,
        public ?float $maxAmount,
        public ?int $gradeLevelId,
        public ?int $classroomId,
        public ?string $startDate,
        public ?string $endDate,
        public ?int $usageLimit,
        public bool $isActive,
    ) {}

    public static function fromRequest(UpdateFeeDiscountRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            code: $request->validated('code') ?: null,
            discountType: $request->validated('discount_type'),
            discountValue: (float) $request->validated('discount_value'),
            maxAmount: $request->validated('max_amount') !== null ? (float) $request->validated('max_amount') : null,
            gradeLevelId: $request->validated('grade_level_id') ? (int) $request->validated('grade_level_id') : null,
            classroomId: $request->validated('classroom_id') ? (int) $request->validated('classroom_id') : null,
            startDate: $request->validated('start_date') ?: null,
            endDate: $request->validated('end_date') ?: null,
            usageLimit: $request->validated('usage_limit') !== null ? (int) $request->validated('usage_limit') : null,
            isActive: (bool) $request->validated('is_active', true),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'code' => $this->code,
            'discount_type' => $this->discountType,
            'discount_value' => $this->discountValue,
            'max_amount' => $this->maxAmount,
            'grade_level_id' => $this->gradeLevelId,
            'classroom_id' => $this->classroomId,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'usage_limit' => $this->usageLimit,
            'is_active' => $this->isActive,
        ];
    }
}
