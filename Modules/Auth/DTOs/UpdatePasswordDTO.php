<?php

namespace Modules\Auth\DTOs;

use Modules\Auth\Http\Requests\UpdatePasswordRequest;

final readonly class UpdatePasswordDTO
{
    public function __construct(
        public string $password,
    ) {}

    public static function fromRequest(UpdatePasswordRequest $request): self
    {
        return new self(
            password: $request->validated('password'),
        );
    }
}
