<?php

namespace Modules\Finance\DTOs;

use Modules\Finance\Http\Requests\StoreFeeCategoryRequest;

final readonly class CreateFeeCategoryDTO
{
    public function __construct(
        public ?string $code,
        public array $name,
        public ?array $description,
        public bool $isActive,
    ) {}

    public static function fromRequest(StoreFeeCategoryRequest $request): self
    {
        return new self(
            code: $request->validated('code') ?: null,
            name: $request->validated('name'),
            description: $request->validated('description'),
            isActive: (bool) $request->validated('is_active', true),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->isActive,
        ];
    }
}
