<?php

namespace Modules\Academic\Transformers;

use Illuminate\Http\Request;
use Modules\Core\Abstracts\BaseResource;

/**
 * @mixin \Modules\Academic\Models\AcademicYear
 */
class AcademicYearResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'is_current' => $this->is_current,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
