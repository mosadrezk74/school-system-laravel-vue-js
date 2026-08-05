<?php

namespace Modules\Student\DTOs;

use Modules\Student\Http\Requests\StoreStudentRequest;

readonly class CreateStudentDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public string $username,
        public string $gender,
        public ?string $birthDate,
        public ?string $nationalId,
        public ?string $phone,
        public bool $isActive,
        public int $academicYearId,
        public int $currentClassId,
        public ?string $admissionDate,
        public ?float $scholarshipPercentage,
        public bool $isGraduated,
        public ?array $extraFields
    ) {}

    public static function fromRequest(StoreStudentRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            email: $request->validated('email'),
            password: $request->validated('password'),
            username: $request->validated('username'),
            gender: $request->validated('gender'),
            birthDate: $request->validated('birth_date'),
            nationalId: $request->validated('national_id'),
            phone: $request->validated('phone'),
            isActive: (bool) $request->validated('is_active', true),
            academicYearId: (int) $request->validated('academic_year_id'),
            currentClassId: (int) $request->validated('current_class_id'),
            admissionDate: $request->validated('admission_date'),
            scholarshipPercentage: $request->validated('scholarship_percentage') ? (float) $request->validated('scholarship_percentage') : null,
            isGraduated: (bool) $request->validated('is_graduated', false),
            extraFields: $request->validated('extra_fields', [])
        );
    }

    public function toUserData(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'password' => bcrypt($this->password),
            'username' => $this->username,
            'gender' => $this->gender,
            'birth_date' => $this->birthDate,
            'national_id' => $this->nationalId,
            'phone' => $this->phone,
            'is_active' => $this->isActive,
            'role' => 'student',
        ];
    }

    public function toProfileData(int $userId): array
    {
        return [
            'user_id' => $userId,
            'academic_year_id' => $this->academicYearId,
            'current_class_id' => $this->currentClassId,
            'admission_date' => $this->admissionDate,
            'scholarship_percentage' => $this->scholarshipPercentage,
            'is_graduated' => $this->isGraduated,
            'extra_fields' => $this->extraFields ? json_encode($this->extraFields) : null,
        ];
    }
}
