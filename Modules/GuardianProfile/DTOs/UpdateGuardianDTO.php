<?php

namespace Modules\GuardianProfile\DTOs;

use Modules\GuardianProfile\Http\Requests\UpdateGuardianRequest;

final readonly class UpdateGuardianDTO
{
    public function __construct(
        // User fields
        public string $name,
        public string $email,
        public string $username,
        public ?string $password,
        public ?string $phone,
        public ?string $nationalId,
        public bool $isActive,

        // GuardianProfile fields
        public ?string $occupation,
    ) {}

    public static function fromRequest(UpdateGuardianRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            email: $request->validated('email'),
            username: $request->validated('username'),
            password: $request->validated('password') ?: null,
            phone: $request->validated('phone'),
            nationalId: $request->validated('national_id'),
            isActive: (bool) $request->validated('is_active', true),
            occupation: $request->validated('occupation'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toProfileArray(): array
    {
        return [
            'occupation' => $this->occupation,
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
            'phone' => $this->phone,
            'national_id' => $this->nationalId,
            'is_active' => $this->isActive,
        ];

        if ($this->password) {
            $data['password'] = $this->password;
        }

        return $data;
    }
}
