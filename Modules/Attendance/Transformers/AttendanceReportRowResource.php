<?php

namespace Modules\Attendance\Transformers;

use Illuminate\Http\Request;
use Modules\Attendance\DTOs\AttendanceReportRowDTO;
use Modules\Core\Abstracts\BaseResource;

/**
 * @mixin AttendanceReportRowDTO
 */
class AttendanceReportRowResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->resource->toArray();
    }
}
