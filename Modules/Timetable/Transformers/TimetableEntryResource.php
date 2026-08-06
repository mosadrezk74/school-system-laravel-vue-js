<?php

namespace Modules\Timetable\Transformers;

use Illuminate\Http\Request;
use Modules\Core\Abstracts\BaseResource;
use Modules\Timetable\Models\TimetableEntry;

/**
 * @mixin TimetableEntry
 */
class TimetableEntryResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'timetable_id' => $this->timetable_id,
            'day_of_week' => $this->day_of_week,
            'period_id' => $this->period_id,
            'period_number' => $this->period_number,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'is_break' => $this->is_break,

            'subject' => $this->whenLoaded('subject', fn () => $this->subject ? [
                'id' => $this->subject->id,
                'name' => $this->subject->name,
            ] : null),

            'teacher' => $this->whenLoaded('teacher', fn () => $this->teacher ? [
                'id' => $this->teacher->id,
                'name' => $this->teacher->name,
            ] : null),

            'classroom' => $this->whenLoaded('classroom', fn () => $this->classroom ? [
                'id' => $this->classroom->id,
                'name' => $this->classroom->name,
            ] : null),

            'period' => $this->whenLoaded('period', fn () => $this->period ? [
                'id' => $this->period->id,
                'period_number' => $this->period->period_number,
                'name' => $this->period->name,
                'start_time' => $this->period->start_time,
                'end_time' => $this->period->end_time,
            ] : null),
        ];
    }
}
