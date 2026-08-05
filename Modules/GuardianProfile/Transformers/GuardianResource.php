<?php

namespace Modules\GuardianProfile\Transformers;

use Illuminate\Http\Request;
use Modules\Core\Abstracts\BaseResource;
use Modules\GuardianProfile\Models\GuardianProfile;

/**
 * @mixin GuardianProfile
 */
class GuardianResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,

            // User fields
            'name' => $this->user->name,
            'email' => $this->user->email,
            'username' => $this->user->username,
            'phone' => $this->user->phone,
            'national_id' => $this->user->national_id,

            // GuardianProfile fields
            'occupation' => $this->occupation,
            'is_active' => $this->is_active,

            'created_at' => $this->created_at?->toIso8601String(),

            'students' => $this->whenLoaded('students', fn () => $this->students->map(fn ($guardianStudent) => [
                'id' => $guardianStudent->id,
                'student_id' => $guardianStudent->student_id,
                'name' => $guardianStudent->student?->user?->name,
                'admission_number' => $guardianStudent->student?->admission_number,
                'relationship' => $guardianStudent->relationship,
                'is_primary_contact' => $guardianStudent->is_primary_contact,
                'can_pickup' => $guardianStudent->can_pickup,
                'receives_notifications' => $guardianStudent->receives_notifications,
            ])->all()),
        ];
    }
}
