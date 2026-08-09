<?php

namespace Modules\Academic\DTOs;

use Illuminate\Http\Request;

final readonly class CreateGradeLevelDTO
{
    /**
     * @param  array<string, string|null>  $name
     * @param  array<string, string|null>|null  $description
     */
    public function __construct(
        public array $name,
        public ?array $description,
        public int $displayOrder,
        public bool $isActive,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->validated('name'),
            description: $request->validated('description'),
            displayOrder: (int) $request->validated('display_order', 0),
            isActive: (bool) $request->validated('is_active', true),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'display_order' => $this->displayOrder,
            'is_active' => $this->isActive,
        ];
    }
}
