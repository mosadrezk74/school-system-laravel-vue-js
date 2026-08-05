<?php

namespace Modules\Auth\DTOs;

use Modules\Auth\Http\Requests\UpdateProfileRequest;

final readonly class UpdateProfileDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $username,
        public ?string $phone,
        public ?string $gender,
        public ?string $birthDate,
        public ?string $nationalId,
        public string $language,
        public string $theme,
        public string $timezone,
    ) {}

    public static function fromRequest(UpdateProfileRequest $request): self
    {
        $data = $request->validated();

        return new self(
            name: $data['name'],
            email: $data['email'],
            username: $data['username'],
            phone: $data['phone'] ?? null,
            gender: $data['gender'] ?? null,
            birthDate: $data['birth_date'] ?? null,
            nationalId: $data['national_id'] ?? null,
            language: $data['language'],
            theme: $data['theme'],
            timezone: $data['timezone'],
        );
    }
}
