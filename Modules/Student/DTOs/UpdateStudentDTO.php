<?php

namespace Modules\Student\DTOs;

use Modules\Academic\Services\AcademicYearService;
use Modules\Student\Http\Requests\UpdateStudentRequest;

final readonly class UpdateStudentDTO
{
    public function __construct(
        // User fields
        public string $name,
        public string $email,
        public string $username,
        public ?string $password,
        public string $gender,
        public ?string $birthDate,
        public ?string $nationalId,
        public ?string $phone,
        public bool $isActive,

        // StudentProfile fields
        public int $academicYearId,
        public ?int $currentClassId,
        public ?string $admissionNumber,
        public ?string $rollNumber,
        public string $admissionDate,
        public ?string $emergencyContactName,
        public ?string $emergencyContactPhone,
        public ?string $emergencyContactRelation,
        public ?string $address,
        public ?string $city,
        public ?string $fatherName,
        public ?string $fatherPhone,
        public ?string $motherName,
        public ?string $motherPhone,
        public ?string $guardianName,
        public ?string $guardianPhone,
        public ?string $guardianRelation,
        public ?string $bloodGroup,
        public ?string $allergies,
        public ?string $medicalConditions,
        public bool $isGraduated,

        /** Share of every fee waived for this student, 0–100. */
        public ?float $scholarshipPercentage,
    ) {}

    public static function fromRequest(UpdateStudentRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            email: $request->validated('email'),
            username: $request->validated('username'),
            password: $request->validated('password') ?: null,
            gender: $request->validated('gender'),
            birthDate: $request->validated('birth_date'),
            nationalId: $request->validated('national_id'),
            phone: $request->validated('phone'),
            isActive: (bool) $request->validated('is_active', true),
            academicYearId: AcademicYearService::requiredId(),
            currentClassId: $request->validated('current_class_id') ? (int) $request->validated('current_class_id') : null,
            admissionNumber: $request->validated('admission_number'),
            rollNumber: $request->validated('roll_number'),
            admissionDate: $request->validated('admission_date'),
            emergencyContactName: $request->validated('emergency_contact_name'),
            emergencyContactPhone: $request->validated('emergency_contact_phone'),
            emergencyContactRelation: $request->validated('emergency_contact_relation'),
            address: $request->validated('address'),
            city: $request->validated('city'),
            fatherName: $request->validated('father_name'),
            fatherPhone: $request->validated('father_phone'),
            motherName: $request->validated('mother_name'),
            motherPhone: $request->validated('mother_phone'),
            guardianName: $request->validated('guardian_name'),
            guardianPhone: $request->validated('guardian_phone'),
            guardianRelation: $request->validated('guardian_relation'),
            bloodGroup: $request->validated('blood_group'),
            allergies: $request->validated('allergies'),
            medicalConditions: $request->validated('medical_conditions'),
            isGraduated: (bool) $request->validated('is_graduated', false),
            scholarshipPercentage: $request->validated('scholarship_percentage') !== null
                ? (float) $request->validated('scholarship_percentage')
                : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toProfileArray(): array
    {
        return [
            'current_class_id' => $this->currentClassId,
            'roll_number' => $this->rollNumber,
            'admission_date' => $this->admissionDate,
            'emergency_contact_name' => $this->emergencyContactName,
            'emergency_contact_phone' => $this->emergencyContactPhone,
            'emergency_contact_relation' => $this->emergencyContactRelation,
            'address' => $this->address,
            'city' => $this->city,
            'father_name' => $this->fatherName,
            'father_phone' => $this->fatherPhone,
            'mother_name' => $this->motherName,
            'mother_phone' => $this->motherPhone,
            'guardian_name' => $this->guardianName,
            'guardian_phone' => $this->guardianPhone,
            'guardian_relation' => $this->guardianRelation,
            'blood_group' => $this->bloodGroup,
            'allergies' => $this->allergies,
            'medical_conditions' => $this->medicalConditions,
            'is_graduated' => $this->isGraduated,
            'scholarship_percentage' => $this->scholarshipPercentage ?? 0,
            'is_active' => $this->isActive,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toUserArray(): array
    {
        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'username' => $this->username,
            'gender' => $this->gender,
            'birth_date' => $this->birthDate,
            'national_id' => $this->nationalId,
            'phone' => $this->phone,
            'is_active' => $this->isActive,
        ];

        if ($this->password) {
            $data['password'] = $this->password;
        }

        return $data;
    }
}
