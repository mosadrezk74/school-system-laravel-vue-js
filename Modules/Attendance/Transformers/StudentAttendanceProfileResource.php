<?php

namespace Modules\Attendance\Transformers;

use Illuminate\Http\Request;
use Modules\Core\Abstracts\BaseResource;
use Modules\Student\Models\StudentProfile;

/**
 * @mixin StudentProfile
 */
class StudentAttendanceProfileResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->user?->name,
            'email' => $this->user?->email,
            'admission_number' => $this->admission_number,
            'academic_year' => $this->academicYear ? [
                'id' => $this->academicYear->id,
                'name' => $this->academicYear->name,
            ] : null,
            'current_class' => $this->currentClass ? [
                'id' => $this->currentClass->id,
                'name' => $this->currentClass->name,
            ] : null,
        ];
    }
}
