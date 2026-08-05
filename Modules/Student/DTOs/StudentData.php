<?php

namespace Modules\Student\DTOs;

use Modules\Student\Http\Requests\StoreStudentRequest;
use Modules\Student\Http\Requests\UpdateStudentRequest;

readonly class StudentData
{
    public function __construct(
        public string $name,
        public string $email,
        public string $username,
        public ?string $password,
        public string $gender,
        public ?string $birthDate,
        public ?string $nationalId,
        public ?string $phone,
        public bool $isActive,
        public ?int $academicYearId,
        public ?int $currentClassId,
        public string $admissionDate,
        public ?float $scholarshipPercentage,
        public bool $isGraduated,
        public ?string $fatherName,
        public ?string $fatherPhone,
        public ?string $motherName,
        public ?string $motherPhone,
        public ?string $guardianName,
        public ?string $guardianPhone,
        public ?string $guardianRelation,
        public ?string $address,
        public ?string $city,
        public ?string $bloodGroup,
        public ?string $allergies,
        public ?string $medicalConditions,
        public ?string $previousSchool,
        public ?string $emergencyContactName,
        public ?string $emergencyContactPhone,
        public ?string $emergencyContactRelation
    ) {}

    public static function fromStoreRequest(StoreStudentRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            email: $request->validated('email'),
            username: $request->validated('username'),
            password: $request->validated('password'),
            gender: $request->validated('gender', 'male'),
            birthDate: $request->validated('birth_date'),
            nationalId: $request->validated('national_id'),
            phone: $request->validated('phone'),
            isActive: (bool) $request->validated('is_active', true),
            academicYearId: $request->validated('academic_year_id') ? (int) $request->validated('academic_year_id') : null,
            currentClassId: $request->validated('current_class_id') ? (int) $request->validated('current_class_id') : null,
            admissionDate: $request->validated('admission_date') ?? now()->toDateString(),
            scholarshipPercentage: $request->validated('scholarship_percentage') ? (float) $request->validated('scholarship_percentage') : null,
            isGraduated: (bool) $request->validated('is_graduated', false),
            fatherName: $request->validated('father_name'),
            fatherPhone: $request->validated('father_phone'),
            motherName: $request->validated('mother_name'),
            motherPhone: $request->validated('mother_phone'),
            guardianName: $request->validated('guardian_name'),
            guardianPhone: $request->validated('guardian_phone'),
            guardianRelation: $request->validated('guardian_relation'),
            address: $request->validated('address'),
            city: $request->validated('city'),
            bloodGroup: $request->validated('blood_group'),
            allergies: $request->validated('allergies'),
            medicalConditions: $request->validated('medical_conditions'),
            previousSchool: $request->validated('previous_school'),
            emergencyContactName: $request->validated('emergency_contact_name'),
            emergencyContactPhone: $request->validated('emergency_contact_phone'),
            emergencyContactRelation: $request->validated('emergency_contact_relation')
        );
    }

    public static function fromUpdateRequest(UpdateStudentRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            email: $request->validated('email'),
            username: $request->validated('username'),
            password: $request->validated('password'),
            gender: $request->validated('gender', 'male'),
            birthDate: $request->validated('birth_date'),
            nationalId: $request->validated('national_id'),
            phone: $request->validated('phone'),
            isActive: (bool) $request->validated('is_active', true),
            academicYearId: $request->validated('academic_year_id') ? (int) $request->validated('academic_year_id') : null,
            currentClassId: $request->validated('current_class_id') ? (int) $request->validated('current_class_id') : null,
            admissionDate: $request->validated('admission_date') ?? now()->toDateString(),
            scholarshipPercentage: $request->validated('scholarship_percentage') ? (float) $request->validated('scholarship_percentage') : null,
            isGraduated: (bool) $request->validated('is_graduated', false),
            fatherName: $request->validated('father_name'),
            fatherPhone: $request->validated('father_phone'),
            motherName: $request->validated('mother_name'),
            motherPhone: $request->validated('mother_phone'),
            guardianName: $request->validated('guardian_name'),
            guardianPhone: $request->validated('guardian_phone'),
            guardianRelation: $request->validated('guardian_relation'),
            address: $request->validated('address'),
            city: $request->validated('city'),
            bloodGroup: $request->validated('blood_group'),
            allergies: $request->validated('allergies'),
            medicalConditions: $request->validated('medical_conditions'),
            previousSchool: $request->validated('previous_school'),
            emergencyContactName: $request->validated('emergency_contact_name'),
            emergencyContactPhone: $request->validated('emergency_contact_phone'),
            emergencyContactRelation: $request->validated('emergency_contact_relation')
        );
    }
}
