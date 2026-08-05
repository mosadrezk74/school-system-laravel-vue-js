<?php

namespace Modules\Student\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Enums\UserRole;
use Modules\Student\DTOs\StudentData;

class StudentRepository
{
    public function getPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return User::query()
            ->where('role', UserRole::Student->value)
            ->with('studentProfile')
            ->latest()
            ->paginate($perPage);
    }

    public function findWithProfile(int $id): User
    {
        return User::query()
            ->where('role', UserRole::Student->value)
            ->with('studentProfile')
            ->findOrFail($id);
    }

    public function create(StudentData $data): User
    {
        return DB::transaction(function () use ($data) {
            $userPayload = [
                'name' => $data->name,
                'email' => $data->email,
                'username' => $data->username,
                'password' => $data->password,
                'gender' => $data->gender,
                'birth_date' => $data->birthDate,
                'national_id' => $data->nationalId,
                'phone' => $data->phone,
                'is_active' => $data->isActive,
                'role' => UserRole::Student->value,
            ];

            $user = User::create($userPayload);

            $user->studentProfile()->create([
                'academic_year_id' => $data->academicYearId,
                'current_class_id' => $data->currentClassId,
                'admission_date' => $data->admissionDate,
                'scholarship_percentage' => $data->scholarshipPercentage,
                'is_graduated' => $data->isGraduated,
                'is_active' => $data->isActive,
                'father_name' => $data->fatherName,
                'father_phone' => $data->fatherPhone,
                'mother_name' => $data->motherName,
                'mother_phone' => $data->motherPhone,
                'guardian_name' => $data->guardianName,
                'guardian_phone' => $data->guardianPhone,
                'guardian_relation' => $data->guardianRelation,
                'address' => $data->address,
                'city' => $data->city,
                'blood_group' => $data->bloodGroup,
                'allergies' => $data->allergies,
                'medical_conditions' => $data->medicalConditions,
                'previous_school' => $data->previousSchool,
                'emergency_contact_name' => $data->emergencyContactName,
                'emergency_contact_phone' => $data->emergencyContactPhone,
                'emergency_contact_relation' => $data->emergencyContactRelation,
            ]);

            return $user;
        });
    }

    public function update(User $user, StudentData $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $userPayload = [
                'name' => $data->name,
                'email' => $data->email,
                'username' => $data->username,
                'gender' => $data->gender,
                'birth_date' => $data->birthDate,
                'national_id' => $data->nationalId,
                'phone' => $data->phone,
                'is_active' => $data->isActive,
            ];

            if ($data->password) {
                $userPayload['password'] = $data->password;
            }

            $user->update($userPayload);

            $user->studentProfile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'academic_year_id' => $data->academicYearId,
                    'current_class_id' => $data->currentClassId,
                    'admission_date' => $data->admissionDate,
                    'scholarship_percentage' => $data->scholarshipPercentage,
                    'is_graduated' => $data->isGraduated,
                    'is_active' => $data->isActive,
                    'father_name' => $data->fatherName,
                    'father_phone' => $data->fatherPhone,
                    'mother_name' => $data->motherName,
                    'mother_phone' => $data->motherPhone,
                    'guardian_name' => $data->guardianName,
                    'guardian_phone' => $data->guardianPhone,
                    'guardian_relation' => $data->guardianRelation,
                    'address' => $data->address,
                    'city' => $data->city,
                    'blood_group' => $data->bloodGroup,
                    'allergies' => $data->allergies,
                    'medical_conditions' => $data->medicalConditions,
                    'previous_school' => $data->previousSchool,
                    'emergency_contact_name' => $data->emergencyContactName,
                    'emergency_contact_phone' => $data->emergencyContactPhone,
                    'emergency_contact_relation' => $data->emergencyContactRelation,
                ]
            );

            return $user;
        });
    }

    public function delete(User $user): bool
    {
        return DB::transaction(function () use ($user) {
            $user->studentProfile()?->delete();
            return (bool) $user->delete();
        });
    }
}
