<?php

namespace Modules\Timetable\Transformers;

use Illuminate\Http\Request;
use Modules\Core\Abstracts\BaseResource;
use Modules\Timetable\Models\Period;

/**
 * @mixin Period
 */
class PeriodResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'period_number' => $this->period_number,
            'name' => $this->name,
            'name_translations' => $this->getTranslations('name'),
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'is_break' => $this->is_break,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
