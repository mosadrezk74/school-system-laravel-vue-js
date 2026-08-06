<?php

namespace Modules\Timetable\DTOs;

use Modules\Timetable\Http\Requests\StorePeriodRequest;

final readonly class CreatePeriodDTO
{
    public function __construct(
        public int $periodNumber,
        public array $name,
        public string $startTime,
        public string $endTime,
        public bool $isBreak,
        public bool $isActive,
    ) {}

    public static function fromRequest(StorePeriodRequest $request): self
    {
        return new self(
            periodNumber: (int) $request->validated('period_number'),
            name: $request->validated('name'),
            startTime: $request->validated('start_time'),
            endTime: $request->validated('end_time'),
            isBreak: (bool) $request->validated('is_break', false),
            isActive: (bool) $request->validated('is_active', true),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'period_number' => $this->periodNumber,
            'name' => $this->name,
            'start_time' => $this->startTime,
            'end_time' => $this->endTime,
            'is_break' => $this->isBreak,
            'is_active' => $this->isActive,
        ];
    }
}
