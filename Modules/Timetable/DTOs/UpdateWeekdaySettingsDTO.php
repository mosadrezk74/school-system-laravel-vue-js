<?php

namespace Modules\Timetable\DTOs;

use Modules\Timetable\Http\Requests\UpdateWeekdaySettingsRequest;

final readonly class UpdateWeekdaySettingsDTO
{
    /**
     * @param  array<int, int>  $activeWeekdays
     */
    public function __construct(
        public array $activeWeekdays,
    ) {}

    public static function fromRequest(UpdateWeekdaySettingsRequest $request): self
    {
        return new self(
            activeWeekdays: array_map('intval', $request->validated('active_weekdays', [])),
        );
    }
}
