<?php

namespace Modules\Finance\DTOs;

use Modules\Finance\Http\Requests\StoreFeeStructureRequest;

final readonly class CreateFeeStructureDTO
{
    public function __construct(
        public int $feeCategoryId,
        public ?int $academicYearId,
        public ?int $semesterId,
        public ?int $gradeLevelId,
        public ?int $classroomId,
        public array $name,
        public float $amount,
        public string $frequency,
        public bool $isOptional,
        public ?int $dueDay,
        public ?float $lateFeeAmount,
        public ?int $lateFeeDays,
        public bool $isActive,
    ) {}

    public static function fromRequest(StoreFeeStructureRequest $request): self
    {
        return new self(
            feeCategoryId: (int) $request->validated('fee_category_id'),
            academicYearId: $request->validated('academic_year_id') ? (int) $request->validated('academic_year_id') : null,
            semesterId: $request->validated('semester_id') ? (int) $request->validated('semester_id') : null,
            gradeLevelId: $request->validated('grade_level_id') ? (int) $request->validated('grade_level_id') : null,
            classroomId: $request->validated('classroom_id') ? (int) $request->validated('classroom_id') : null,
            name: $request->validated('name'),
            amount: (float) $request->validated('amount'),
            frequency: $request->validated('frequency'),
            isOptional: (bool) $request->validated('is_optional', false),
            dueDay: $request->validated('due_day') !== null ? (int) $request->validated('due_day') : null,
            lateFeeAmount: $request->validated('late_fee_amount') !== null ? (float) $request->validated('late_fee_amount') : null,
            lateFeeDays: $request->validated('late_fee_days') !== null ? (int) $request->validated('late_fee_days') : null,
            isActive: (bool) $request->validated('is_active', true),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'fee_category_id' => $this->feeCategoryId,
            'academic_year_id' => $this->academicYearId,
            'semester_id' => $this->semesterId,
            'grade_level_id' => $this->gradeLevelId,
            'classroom_id' => $this->classroomId,
            'name' => $this->name,
            'amount' => $this->amount,
            'frequency' => $this->frequency,
            'is_optional' => $this->isOptional,
            'due_day' => $this->dueDay,
            'late_fee_amount' => $this->lateFeeAmount,
            'late_fee_days' => $this->lateFeeDays,
            'is_active' => $this->isActive,
        ];
    }
}
