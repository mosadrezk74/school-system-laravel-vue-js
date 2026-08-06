<?php

namespace Modules\Timetable\DTOs;

use Modules\Timetable\Http\Requests\UpdateTimetableEntriesRequest;

final readonly class UpsertTimetableEntriesDTO
{
    /**
     * @param  array<int, TimetableEntryDTO>  $entries
     */
    public function __construct(
        public int $timetableId,
        public array $entries,
    ) {}

    public static function fromRequest(UpdateTimetableEntriesRequest $request, int $timetableId): self
    {
        $entries = array_map(
            fn (array $row): TimetableEntryDTO => TimetableEntryDTO::fromArray($row),
            $request->validated('entries', [])
        );

        return new self(
            timetableId: $timetableId,
            entries: $entries,
        );
    }
}
