<?php

namespace Modules\Student\Transformers;

use Illuminate\Http\Request;
use Modules\Core\Abstracts\BaseResource;
use Modules\Student\Models\StudentProfile;

/**
 * @mixin StudentProfile
 */
class StudentResource extends BaseResource
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
            'gender' => $this->user->gender,
            'birth_date' => $this->user->birth_date?->toDateString(),
            'national_id' => $this->user->national_id,
            'phone' => $this->user->phone,

            // StudentProfile fields
            'academic_year_id' => $this->academic_year_id,
            'current_class_id' => $this->current_class_id,
            'admission_number' => $this->admission_number,
            'roll_number' => $this->roll_number,
            'admission_date' => $this->admission_date?->toDateString(),

            'emergency_contact_name' => $this->emergency_contact_name,
            'emergency_contact_phone' => $this->emergency_contact_phone,
            'emergency_contact_relation' => $this->emergency_contact_relation,
            'address' => $this->address,
            'city' => $this->city,

            'father_name' => $this->father_name,
            'father_phone' => $this->father_phone,
            'mother_name' => $this->mother_name,
            'mother_phone' => $this->mother_phone,
            'guardian_name' => $this->guardian_name,
            'guardian_phone' => $this->guardian_phone,
            'guardian_relation' => $this->guardian_relation,

            'blood_group' => $this->blood_group,
            'allergies' => $this->allergies,
            'medical_conditions' => $this->medical_conditions,

            'is_graduated' => $this->is_graduated,
            'scholarship_percentage' => $this->scholarship_percentage,
            'is_active' => $this->is_active,

            'created_at' => $this->created_at?->toIso8601String(),

            'academic_year' => $this->whenLoaded('academicYear', fn () => [
                'id' => $this->academicYear->id,
                'name' => $this->academicYear->name,
            ]),

            'current_class' => $this->whenLoaded('currentClass', fn () => $this->currentClass ? [
                'id' => $this->currentClass->id,
                'name' => $this->currentClass->name,
                'grade_level' => $this->currentClass->relationLoaded('gradeLevel') ? [
                    'id' => $this->currentClass->gradeLevel?->id,
                    'name' => $this->currentClass->gradeLevel?->name,
                ] : null,
            ] : null),

            'guardians' => $this->whenLoaded('guardians', fn () => $this->guardians->map(fn ($guardianStudent) => [
                'id' => $guardianStudent->id,
                'parent_id' => $guardianStudent->parent_id,
                'relationship' => $guardianStudent->relationship,
                'is_primary_contact' => $guardianStudent->is_primary_contact,
                'name' => $guardianStudent->parent?->user?->name,
                'phone' => $guardianStudent->parent?->user?->phone,
            ])->all()),
        ];
    }
}
