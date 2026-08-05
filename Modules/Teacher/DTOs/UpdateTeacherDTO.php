<?php

namespace Modules\Teacher\DTOs;

use Illuminate\Http\Request;
use Modules\Core\Abstracts\BaseDTO;

final class UpdateTeacherDTO extends BaseDTO
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
        public ?string $employeeId,
        public ?string $qualification,
        public ?string $specialization,
        public int $experienceYears,
        public ?string $joiningDate,
        public ?int $departmentId,
        public ?string $position,
        public ?string $salaryType,
        public ?float $salaryBase,
        public ?array $primarySubjects,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->validated('name'),
            email: $request->validated('email'),
            username: $request->validated('username'),
            password: $request->validated('password'),
            gender: $request->validated('gender'),
            birthDate: $request->validated('birth_date'),
            nationalId: $request->validated('national_id'),
            phone: $request->validated('phone'),
            isActive: (bool) $request->validated('is_active', true),
            employeeId: $request->validated('employee_id'),
            qualification: $request->validated('qualification'),
            specialization: $request->validated('specialization'),
            experienceYears: (int) $request->validated('experience_years', 0),
            joiningDate: $request->validated('joining_date'),
            departmentId: $request->validated('department_id') ? (int) $request->validated('department_id') : null,
            position: $request->validated('position'),
            salaryType: $request->validated('salary_type'),
            salaryBase: $request->validated('salary_base') ? (float) $request->validated('salary_base') : null,
            primarySubjects: $request->validated('primary_subjects'),
        );
    }

    public function toArray(): array
    {
        return [
            'employee_id' => $this->employeeId,
            'qualification' => $this->qualification,
            'specialization' => $this->specialization,
            'experience_years' => $this->experienceYears,
            'joining_date' => $this->joiningDate,
            'department_id' => $this->departmentId,
            'position' => $this->position,
            'salary_type' => $this->salaryType,
            'salary_base' => $this->salaryBase,
            'primary_subjects' => $this->primarySubjects,
            'is_active' => $this->isActive,
        ];
    }

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
