<?php

namespace Modules\Academic\DTOs;

use Illuminate\Http\Request;

final readonly class CreateSemesterDTO
{
    /**
     * @param  array<string, string|null>  $name
     */
    public function __construct(
        public int $academicYearId,
        public array $name,
        public int $sequence,
        public string $startDate,
        public string $endDate,
        public ?string $examStartDate,
        public ?string $examEndDate,
        public bool $isActive,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            academicYearId: (int) $request->validated('academic_year_id'),
            name: $request->validated('name'),
            sequence: (int) $request->validated('sequence'),
            startDate: $request->validated('start_date'),
            endDate: $request->validated('end_date'),
            examStartDate: $request->validated('exam_start_date') ?: null,
            examEndDate: $request->validated('exam_end_date') ?: null,
            isActive: (bool) $request->validated('is_active', true),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'academic_year_id' => $this->academicYearId,
            'name' => $this->name,
            'sequence' => $this->sequence,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'exam_start_date' => $this->examStartDate,
            'exam_end_date' => $this->examEndDate,
            'is_active' => $this->isActive,
        ];
    }
}
