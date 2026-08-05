<?php

namespace Modules\Teacher\Transformers;

use Modules\Core\Abstracts\BaseResource;

class TeacherResource extends BaseResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->user?->name,
            'email' => $this->user?->email,
            'username' => $this->user?->username,
            'gender' => $this->user?->gender,
            'birth_date' => $this->user?->birth_date,
            'national_id' => $this->user?->national_id,
            'phone' => $this->user?->phone,
            'is_active' => (bool) $this->is_active,
            'employee_id' => $this->employee_id,
            'qualification' => $this->qualification,
            'specialization' => $this->specialization,
            'experience_years' => $this->experience_years,
            'joining_date' => $this->joining_date,
            'department_id' => $this->department_id,
            'department' => $this->whenLoaded('department', fn () => [
                'id' => $this->department->id,
                'name' => $this->department->name,
            ]),
            'position' => $this->position,
            'salary_type' => $this->salary_type,
            'salary_base' => $this->salary_base ? (float) $this->salary_base : null,
            'primary_subjects' => $this->primary_subjects,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}
