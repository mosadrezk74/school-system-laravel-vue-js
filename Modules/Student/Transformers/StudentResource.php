<?php

namespace Modules\Student\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'username' => $this->username,
            'gender' => $this->gender,
            'is_active' => (bool) $this->is_active,
            'academic_year_id' => $this->studentProfile?->academic_year_id,
            'current_class_id' => $this->studentProfile?->current_class_id,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
