<?php

namespace Modules\Auth\DTOs;

use Illuminate\Http\Request;

final readonly class CreateAdminDTO
{
    /**
     * @param  array<int, string>  $roles  Spatie role names to assign.
     */
    public function __construct(
        public string $name,
        public string $email,
        public ?string $username,
        public string $password,
        public ?string $phone,
        public ?string $gender,
        public ?string $birthDate,
        public ?string $nationalId,
        public bool $isActive,
        public string $accountRole,
        public array $roles,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->validated('name'),
            email: $request->validated('email'),
            username: $request->validated('username'),
            password: $request->validated('password'),
            phone: $request->validated('phone'),
            gender: $request->validated('gender'),
            birthDate: $request->validated('birth_date'),
            nationalId: $request->validated('national_id'),
            isActive: (bool) $request->validated('is_active', true),
            accountRole: (string) $request->validated('account_role'),
            roles: array_values(array_unique($request->validated('roles', []))),
        );
    }

    /**
     * The `role` column is set by the Action from $accountRole after it has
     * been authorised, never straight from here.
     *
     * @return array<string, mixed>
     */
    public function toUserArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'username' => $this->username,
            // User::password is cast 'hashed', so this is hashed on save.
            'password' => $this->password,
            'phone' => $this->phone,
            'gender' => $this->gender,
            'birth_date' => $this->birthDate,
            'national_id' => $this->nationalId,
            'is_active' => $this->isActive,
        ];
    }
}
