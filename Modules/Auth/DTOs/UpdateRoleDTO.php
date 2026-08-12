<?php

namespace Modules\Auth\DTOs;

use Illuminate\Http\Request;

final readonly class UpdateRoleDTO
{
    /**
     * @param  array<int, string>  $permissions
     */
    public function __construct(
        public ?string $name,
        public array $permissions,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $name = $request->validated('name');

        return new self(
            name: is_string($name) ? trim($name) : null,
            permissions: array_values(array_unique($request->validated('permissions', []))),
        );
    }
}
