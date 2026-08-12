<?php

namespace Modules\Auth\DTOs;

use Illuminate\Http\Request;

final readonly class UpdateAdminDTO
{
    /**
     * @param  array<int, string>  $roles
     */
    public function __construct(
        public string $name,
        public string $email,
        public ?string $username,
        public ?string $password,
        public ?string $phone,
        public ?string $gender,
        public ?string $birthDate,
        public ?string $nationalId,
        public bool $isActive,
        public ?string $accountRole,
        public array $roles,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $password = $request->validated('password');

        return new self(
            name: $request->validated('name'),
            email: $request->validated('email'),
            username: $request->validated('username'),
            // Blank means "leave the current password alone".
            password: is_string($password) && $password !== '' ? $password : null,
            phone: $request->validated('phone'),
            gender: $request->validated('gender'),
            birthDate: $request->validated('birth_date'),
            nationalId: $request->validated('national_id'),
            isActive: (bool) $request->validated('is_active', true),
            accountRole: $request->validated('account_role'),
            roles: array_values(array_unique($request->validated('roles', []))),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toUserArray(): array
    {
        $attributes = [
            'name' => $this->name,
            'email' => $this->email,
            'username' => $this->username,
            'phone' => $this->phone,
            'gender' => $this->gender,
            'birth_date' => $this->birthDate,
            'national_id' => $this->nationalId,
            'is_active' => $this->isActive,
        ];

        if ($this->password !== null) {
            $attributes['password'] = $this->password;
        }

        return $attributes;
    }
}
