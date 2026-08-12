<?php

namespace Modules\Auth\DTOs;

use Illuminate\Http\Request;

final readonly class CreateRoleDTO
{
    /**
     * @param  array<int, string>  $permissions
     */
    public function __construct(
        public string $name,
        public array $permissions,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: trim((string) $request->validated('name')),
            permissions: array_values(array_unique($request->validated('permissions', []))),
        );
    }

    /**
     * Only the columns the roles table owns — permissions are synced through
     * the pivot, not mass-assigned.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
        ];
    }
}
